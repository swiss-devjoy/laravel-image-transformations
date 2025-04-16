<?php

namespace SwissDevjoy\LaravelImageTransformations;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelImageTransformationsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-image-transformations')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        Route::get(rtrim(config('image-transformations.url_prefix')).'/{options}/{path}', LaravelImageTransformationsController::class)
            ->where('options', '([a-z]+=[a-z0-9]+,?)+')
            ->where('path', '.*\..*')
            ->withoutMiddleware('web')
            ->name('laravel-image-transformation-url');

        Route::get(rtrim(config('image-transformations.url_prefix')).'/{signature?}/{options}/{path}', LaravelImageTransformationsController::class)
            ->where('options', '([a-z]+=[a-z0-9]+,?)+')
            ->where('path', '.*\..*')
            ->withoutMiddleware('web')
            ->name('laravel-image-transformation-signed-url');
    }
}
