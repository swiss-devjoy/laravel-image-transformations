<?php

namespace SwissDevjoy\LaravelImageTransformations\Tests;

use Intervention\Image\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use SwissDevjoy\LaravelImageTransformations\LaravelImageTransformationsServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            LaravelImageTransformationsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app) {}
}
