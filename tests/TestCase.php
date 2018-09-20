<?php

declare(strict_types=1);

namespace Tests;

use Mockery;
use Mockery\MockInterface;
use Algolia\AlgoliaSearch\Index;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\AlgoliaEngine;
use Algolia\LaravelScoutExtended\Facades\Algolia;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;

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

        $clientMock = Mockery::mock(Algolia::client())->makePartial();

        $clientMock->expects('initIndex')->with((new $model)->searchableAs())->andReturn($indexMock);

        $engineMock = Mockery::mock(AlgoliaEngine::class, [$clientMock])->makePartial();

        $managerMock = Mockery::mock(EngineManager::class)->makePartial();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(ClientInterface::class, $clientMock);

        $this->swap(EngineManager::class, $managerMock);

        return $indexMock;
    }
}
