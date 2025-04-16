<?php

use Intervention\Image\Laravel\Facades\Image;

beforeEach(function () {
    $this->app->usePublicPath(__DIR__.'/public');
});

test('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

test('transformImage helper generates correct signed URL', function () {
    $url = transformImage('/images/test-image.png', 'width=300,format=webp');

    expect($url)->toMatch('#^/img/[a-z0-9]{64}/width=300,format=webp/images/test-image.png$#');
});

test('transformImage helper returns regular URL when signing is disabled', function () {
    config(['image-transformations.signed_urls' => false]);

    $url = transformImage('/images/test-image.png', 'width=300,format=webp');

    expect($url)->toEqual('/img/width=300,format=webp/images/test-image.png');
});

test('transformImage helper returns original path when transformations are disabled', function () {
    config(['image-transformations.enabled' => false]);

    $url = transformImage('/images/test-image.png', 'width=300,format=webp');

    expect($url)->toEqual('/images/test-image.png');
});

test('request to transformed image returns transformed image with correct headers', function () {
    config(['image-transformations.signed_urls' => false]);

    $response = $this->get('/img/width=300,format=webp/images/dimitri-koenig.jpg')
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/webp');

    $cacheControlHeader = $response->headers->get('Cache-Control');

    // cache-control header is sorted, therefore we need to check for all values separately
    expect($cacheControlHeader)->toContain('public')
        ->and($cacheControlHeader)->toContain('max-age=2592000')
        ->and($cacheControlHeader)->toContain('s-maxage=2592000')
        ->and($cacheControlHeader)->toContain('immutable');
});

test('controller validates signed URLs', function () {
    $signedUrl = transformImage('/images/dimitri-koenig.jpg', 'width=300,format=webp');

    $this->get($signedUrl)->assertStatus(200);

    // Invalid signature should fail
    $invalidUrl = str_replace('width=300', 'width=400', $signedUrl);
    $this->get($invalidUrl)->assertStatus(403);
});

test('controller applies rate limiting when enabled', function () {
    config([
        'image-transformations.signed_urls' => false,
        'image-transformations.ratelimiter.enabled' => true,
    ]);

    $url = transformImage('/images/dimitri-koenig.jpg', 'width=300,format=webp');

    // First request should pass
    $this->get($url)->assertStatus(200);

    // Second request should pass
    $this->get($url)->assertStatus(200);

    // Third request should redirect to original image
    $this->get($url)
        ->assertStatus(307)
        ->assertRedirect('/images/dimitri-koenig.jpg');
});

test('controller does not apply rate limiting when signed urls are used', function () {
    config([
        'image-transformations.signed_urls' => true,
        'image-transformations.ratelimiter.enabled' => true,
    ]);

    $signedUrl = transformImage('/images/dimitri-koenig.jpg', 'width=300,format=webp');

    // All 3 requests with valid url should pass
    $this->get($signedUrl)->assertStatus(200);
    $this->get($signedUrl)->assertStatus(200);
    $this->get($signedUrl)->assertStatus(200);

    // All 3 requests with invalid url should return 403 without redirecting to original image
    $invalidUrl = str_replace('width=300', 'width=400', $signedUrl);
    $this->get($invalidUrl)->assertStatus(403);
    $this->get($invalidUrl)->assertStatus(403);
    $this->get($invalidUrl)->assertStatus(403);
});

test('transform method applies correct image transformations', function () {
    $url = transformImage('/images/dimitri-koenig.jpg', 'width=100,format=png');

    $imageContent = $this->get($url)->content();

    $image = Image::read($imageContent);

    expect($image->width())->toBe(100)
        ->and($image->origin()->mimetype())->toBe('image/png');
});
