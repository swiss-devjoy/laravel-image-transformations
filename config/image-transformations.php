<?php

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
