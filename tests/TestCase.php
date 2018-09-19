<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Laravel\Scout\ScoutServiceProvider::class,
            \Algolia\LaravelScoutExtended\LaravelScoutExtendedServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Algolia' => \Algolia\LaravelScoutExtended\Facades\Algolia::class,
        ];
    }
}
