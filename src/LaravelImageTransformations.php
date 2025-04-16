<?php

namespace SwissDevjoy\LaravelImageTransformations;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;

class LaravelImageTransformations
{
    public function signedUrl(string $imagePath, string $optionsString): string
    {
        if (! config('image-transformations.enabled')) {
            return $imagePath;
        }

        $imagePath = trim($imagePath, '/ ');

        return config('image-transformations.signed_urls')
            ? Url::signedRoute('laravel-image-transformation-signed-url', ['path' => $imagePath, 'options' => $optionsString], absolute: false)
            : Url::route('laravel-image-transformation-url', ['path' => $imagePath, 'options' => $optionsString], absolute: false);
    }

    public function transform(string $imagePath, string $optionsString): EncodedImageInterface
    {
        $image = $this->getImage($imagePath);
        $options = $this->parseOptions($optionsString);

        if (Arr::hasAny($options, ['width', 'height'])) {
            $width = $options['width'] ?? null;
            $height = $options['height'] ?? null;

            $image->scaleDown(width: $width, height: $height);
        }

        if ($blur = (int) Arr::get($options, 'blur')) {
            $image->blur($blur);
        }

        if ($rotate = (int) Arr::get($options, 'rotate')) {
            $image->rotate($rotate);
        }

        if ($brightness = (int) Arr::get($options, 'brightness')) {
            $image->brightness($brightness);
        }

        if ($contrast = (int) Arr::get($options, 'contrast')) {
            $image->contrast($contrast);
        }

        $quality = (int) Arr::get($options, 'quality', config('image-transformations.default_quality', 90));
        $format = Arr::get($options, 'format');

        return $image->encodeByExtension($format, quality: $quality);
    }

    protected function getImage(string $imagePath): ImageInterface
    {
        $fullImagePath = public_path($imagePath);
        abort_if(! File::exists($fullImagePath), 404);

        return Image::read($fullImagePath);
    }

    protected function parseOptions(string $options): array
    {
        $options = strtolower($options);

        return collect(explode(',', $options))
            ->mapWithKeys(function ($opt) {
                try {
                    $parts = explode('=', $opt, 2);

                    return [$parts[0] => $parts[1]];
                } catch (\Throwable $e) {
                    return [];
                }
            })
            ->toArray();
    }
}
