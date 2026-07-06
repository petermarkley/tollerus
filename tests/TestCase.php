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

    protected function defineEnvironment($app): void
    {
        $app['config']->set('tollerus.admin_middleware', ['web']);
        $app['config']->set('tollerus.enable_queue', false);
    }
}
