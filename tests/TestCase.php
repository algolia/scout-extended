<?php

declare(strict_types=1);

namespace Tests;

use Mockery;
use function get_class;
use Mockery\MockInterface;
use Algolia\AlgoliaSearch\Index;
use Laravel\Scout\EngineManager;
use Algolia\AlgoliaSearch\Client;
use Laravel\Scout\Engines\AlgoliaEngine;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Algolia\AlgoliaSearch\Response\AbstractResponse;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;

class TestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->setBasePath(__DIR__.'/laravel');

        $this->withFactories(database_path('factories'));

        @unlink(config_path('scout-users.php'));
    }

    public function tearDown()
    {
        parent::tearDown();

        @unlink(__DIR__ . '/laravel/config/scout-users.php');
    }

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

    protected function getRemoteDefaultSettings(): array
    {
        $defaultsIndex = $this->mockIndex('temp-laravel-scout-extended');
        $defaults = require __DIR__.'/resources/defaults.php';
        $defaultsIndex->shouldReceive('getSettings')->zeroOrMoreTimes()->andReturn($defaults);
        $this->app->get(ClientInterface::class)->shouldReceive('deleteIndex')->with('temp-laravel-scout-extended')->zeroOrMoreTimes();

        return $defaults;
    }

    protected function mockEngine(): MockInterface
    {
        $engineMock = Mockery::mock(AlgoliaEngine::class)->makePartial()->shouldIgnoreMissing();

        $managerMock = Mockery::mock(EngineManager::class)->makePartial()->shouldIgnoreMissing();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(EngineManager::class, $managerMock);

        return $engineMock;
    }

    protected function mockIndex(string $model): MockInterface
    {
        $indexMock = Mockery::mock(Index::class);

        $client = $this->app->get(ClientInterface::class);

        $clientMock = get_class($client) === 'Algolia\AlgoliaSearch\Client' ? Mockery::mock(Client::class) : $client;

        $clientMock->shouldReceive('initIndex')->zeroOrMoreTimes()->with(class_exists($model) ? (new $model)->searchableAs() : $model)->andReturn($indexMock);

        $engineMock = Mockery::mock(AlgoliaEngine::class, [$clientMock])->makePartial();

        $managerMock = Mockery::mock(EngineManager::class)->makePartial();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(ClientInterface::class, $clientMock);

        $this->swap(EngineManager::class, $managerMock);

        return $indexMock;
    }
}
