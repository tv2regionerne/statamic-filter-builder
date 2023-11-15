<?php

namespace Tests;

use Tv2regionerne\StatamicFilterBuilder\ServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            \Statamic\Providers\StatamicServiceProvider::class,
        ];
    }
}
