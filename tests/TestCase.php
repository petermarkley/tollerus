<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Livewire\LivewireServiceProvider;
use PeterMarkley\Tollerus\Providers\TollerusServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            TollerusServiceProvider::class,
        ];
    }
}
