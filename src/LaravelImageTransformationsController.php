<?php

namespace SwissDevjoy\LaravelImageTransformations;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Interfaces\EncodedImageInterface;
use SwissDevjoy\LaravelImageTransformations\Facades\LaravelImageTransformations;

class LaravelImageTransformationsController
{
    protected Request $request;

    public function __invoke(Request $request): Response
    {
        $this->request = $request;

        $this->validateRequest();

        $transformedImage = LaravelImageTransformations::transform($request->route('path'), $request->route('options'));

        return $this->response($transformedImage);
    }

    protected function validateRequest(): void
    {
        if (! config('image-transformations.enabled')) {
            $this->abortToOriginalImage();
        }

        if (config('image-transformations.signed_urls')) {
            $this->validateSignature();
        } elseif (config('image-transformations.ratelimiter.enabled')) {
            $this->checkRateLimit();
        }
    }

    protected function abortToOriginalImage(): void
    {
        throw new HttpResponseException(Redirect::to($this->request->route('path'), 307));
    }

    protected function validateSignature(): bool
    {
        $signature = $this->request->route('signature');
        abort_if(! $signature, app()->isProduction() ? 404 : 403);

        $urlWithoutSignature = route('laravel-image-transformation-signed-url', ['path' => $this->request->route('path'), 'options' => $this->request->route('options')], absolute: false);

        $keys = [config('app.key'), ...(config('app.previous_keys') ?? [])];

        foreach ($keys as $key) {
            if (hash_equals(
                hash_hmac('sha256', $urlWithoutSignature, $key),
                $signature
            )) {
                return true;
            }
        }

        abort(app()->isProduction() ? 404 : 403);
    }

    protected function checkRateLimit(): void
    {
        $cacheStore = config('image-transformations.ratelimiter.store', config('cache.limiter', 'file'));
        $rateLimiter = new RateLimiter(Cache::store($cacheStore));

        $rateLimiterKey = 'image-transformation:'.$this->request->ip().':'.$this->request->route('path');
        $maxAttempts = config('image-transformations.ratelimiter.max_attempts', 2);

        $allowed = $rateLimiter->attempt($rateLimiterKey, $maxAttempts, callback: fn () => true);

        if (! $allowed) {
            $this->abortToOriginalImage();
        }
    }

    protected function response(EncodedImageInterface $transformedImage): Response
    {
        $response = response($transformedImage, 200)->header('Content-Type', $transformedImage->mimetype());

        if (config('image-transformations.cache_control_headers')) {
            $response->header('Cache-Control', config('image-transformations.cache_control_headers'));
        }

        return $response;
    }
}
