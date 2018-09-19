<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Scout\EngineManager;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('scout.driver', 'algolia');

        $app['config']->set('scout.algolia', [
            'id' => getenv('ALGOLIA_APP_ID'),
            'secret' => getenv('ALGOLIA_SECRET'),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Laravel\Scout\ScoutServiceProvider::class,
            \Algolia\LaravelScoutExtended\LaravelScoutExtendedServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Algolia' => \Algolia\LaravelScoutExtended\Facades\Algolia::class
        ];
    }

    protected function refreshApplication()
    {
        parent::refreshApplication();

        $this->app->singleton(EngineManager::class, function ($app) {
            return new EngineManagerDouble($app);
        });
    }
}
