<?php

use SwissDevjoy\LaravelImageTransformations\Facades\LaravelImageTransformations;

if (! function_exists('transformImage')) {
    function transformImage(string $path, string $options): string
    {
        return LaravelImageTransformations::signedUrl($path, $options);
    }
}
