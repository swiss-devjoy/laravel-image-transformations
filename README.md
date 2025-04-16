# Secure Cloudflare-like Image Transformations for your Laravel App

[![Latest Version on Packagist](https://img.shields.io/packagist/v/swiss-devjoy/laravel-image-transformations.svg?style=flat-square)](https://packagist.org/packages/swiss-devjoy/laravel-image-transformations)
[![Total Downloads](https://img.shields.io/packagist/dt/swiss-devjoy/laravel-image-transformations.svg?style=flat-square)](https://packagist.org/packages/swiss-devjoy/laravel-image-transformations)

Add Cloudflare-like image transformations with security features to your app, inspired by [Aaron Francis's image proxy implementation](https://aaronfrancis.com/2025/a-cookieless-cache-friendly-image-proxy-in-laravel-inspired-by-cloudflare-9e95f7e0).

## Features

- Transform images on-the-fly with simple URL parameters
- Support for various transformations (resize, blur, rotate, brightness, contrast, format conversion, quality)
- Secure URL signing to prevent abuse
- Rate limiting option for unsigned URLs to prevent abuse
- Browser and CDN-friendly caching
- Zero disk storage for transformed images (HTTP caching only)

## Installation

You can install the package via composer:

```bash
composer require swiss-devjoy/laravel-image-transformations
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-image-transformations-config"
```

## Configuration

The package comes with sensible defaults, but you can customize the behavior:

```php
return [
    // Enable/disable all image transformations
    'enabled' => env('IMAGE_TRANSFORMATIONS_ENABLED', true),

    // Cache control headers for browser and CDN caching
    // Sets 30-day cache with immutable flag to improve performance
    'cache_control_headers' => 'public, max-age=2592000, s-maxage=2592000, immutable',

    // URL route prefix for all transformed images
    // Example: /img/options/path-to-image.jpg
    'url_prefix' => '/img',

    // Default compression quality (1-100) for supported formats
    // Higher values mean better quality but larger file sizes
    'default_quality' => 90,

    // Security option 1: Cryptographic URL signing
    // When enabled, prevents unauthorized image transformations by adding a signature hash to URLs
    // Example: /img/[signature-hash]/options/path-to-image.jpg
    'signed_urls' => true,

    // Security option 2: Rate limiting
    // Controls the frequency of transformation requests from the same client
    // Note: Use either signed_urls OR ratelimiter (not both simultaneously)
    'ratelimiter' => [
        // Enable rate limiting for transformation requests
        // Must be disabled when signed_urls is enabled
        'enabled' => false,

        // Cache store for rate limiter counters
        // File-based store recommended to avoid edge case issues like database locks
        // Options: 'file', 'redis', 'database', etc. from config('cache.stores')
        'store' => env('IMAGE_TRANSFORMATIONS_RATELIMITER_STORE', 'file'),

        // Maximum transformation requests allowed per minute per IP
        // Adjust based on your application's needs and expected traffic
        'max_attempts' => 2,
    ],
];
```

## Usage

### Basic Example

Original image reference:

```blade
<img src="/images/profile.jpg" width="500" height="500">
```

With transformation:
```blade
<img src="{{ transformImage('images/profile.jpg', 'width=300,format=webp,quality=80') }}" width="300" height="300">
```

### Transformation Options

| Option | Description | Example |
|--------|-------------|---------|
| `width` | Scale down to specified width (maintains aspect ratio) | `width=300` |
| `height` | Scale down to specified height (maintains aspect ratio) | `height=200` |
| `format` | Convert image format | `format=webp` |
| `quality` | Set compression quality (1-100) | `quality=80` |
| `blur` | Apply blur effect (1-100) | `blur=10` |
| `rotate` | Rotate image (degrees) | `rotate=90` |
| `brightness` | Adjust brightness | `brightness=15` |
| `contrast` | Adjust contrast | `contrast=25` |

## Security Options

### Option 1: Signed URLs (Recommended)

Secure your transformations with cryptographic signatures:

```php
// config/image-transformations.php
'signed_urls' => true,
```

This generates a signed URL like:
```
/img/100000b3fe067c19625e10ccb1959320e65a7d34d43d9d4d48d3df95691f6f03/width=300,format=webp,quality=80/images/profile.jpg
```

### Option 2: Rate Limiting

If you prefer not to use signatures:

```php
// config/image-transformations.php
'signed_urls' => false,
'ratelimiter' => [
    'enabled' => true,
],
```

Direct URLs:
```
/img/width=300,format=webp,quality=80/images/profile.jpg
```

## Performance Considerations

- Images are transformed on-the-fly
- No disk storage is used for transformed images
- HTTP caching with appropriate headers (30 days by default)
- Works best with CDNs for edge caching

## Example Use Cases

### Responsive Images

```blade
<picture>
    <source srcset="{{ transformImage('images/hero.jpg', 'width=1200,format=webp') }}" media="(min-width: 800px)" type="image/webp">
    <source srcset="{{ transformImage('images/hero.jpg', 'width=800,format=webp') }}" media="(min-width: 600px)" type="image/webp">
    <img src="{{ transformImage('images/hero.jpg', 'width=400,format=webp') }}" alt="Hero image">
</picture>
```

### Creative Effects

```blade
<img src="{{ transformImage('images/profile.jpg', 'blur=5,contrast=10,brightness=5') }}" alt="Stylized profile">
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Dimitri König](https://github.com/dimitri-koenig)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
