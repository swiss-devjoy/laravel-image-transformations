<?php

namespace SwissDevjoy\LaravelImageTransformations\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SwissDevjoy\LaravelImageTransformations\LaravelImageTransformations
 */
class LaravelImageTransformations extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SwissDevjoy\LaravelImageTransformations\LaravelImageTransformations::class;
    }
}
