<?php

declare(strict_types=1);

namespace Tests;

use function get_class;
use Mockery\MockInterface;
use Algolia\AlgoliaSearch\Index;
use Laravel\Scout\EngineManager;
use Algolia\AlgoliaSearch\Client;
use Laravel\Scout\Engines\AlgoliaEngine;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Algolia\LaravelScoutExtended\Settings\Compiler;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;

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

        @unlink(__DIR__.'/laravel/config/scout-users.php');
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

    protected function defaults(): array
    {
        $this->mockIndex('temp-laravel-scout-extended', $defaults = require __DIR__.'/resources/defaults.php');
        $this->app->get(ClientInterface::class)->shouldReceive('deleteIndex')->with('temp-laravel-scout-extended')->zeroOrMoreTimes();

        return $defaults;
    }

    protected function assertLocalHas(array $settings): void
    {
        $this->assertFileExists(config_path('scout-users.php'));
        $this->assertEquals($settings, require config_path('scout-users.php'));
    }

    protected function local(): array
    {
        $viewVariables = array_fill_keys(Compiler::getViewVariables(), null);

        return array_merge($viewVariables, [
            'searchableAttributes' => [
                'name',
                'email',
            ],
            'queryLanguages' => ['en'],
        ]);
    }

    protected function localMd5(): string
    {
        $content = $this->local();

        ksort($content);

        return md5(serialize($content));
    }

    protected function mockEngine(): MockInterface
    {
        $engineMock = mock(AlgoliaEngine::class)->makePartial()->shouldIgnoreMissing();

        $managerMock = mock(EngineManager::class)->makePartial()->shouldIgnoreMissing();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(EngineManager::class, $managerMock);

        return $engineMock;
    }

    protected function mockIndex(string $model, array $settings = []): MockInterface
    {
        $indexMock = mock(Index::class);
        $indexName = class_exists($model) ? (new $model)->searchableAs() : $model;
        $indexMock->shouldReceive('getIndexName')->zeroOrMoreTimes()->andReturn($indexName);
        $indexMock->shouldReceive('getSettings')->zeroOrMoreTimes()->andReturn($settings);

        $client = $this->app->get(ClientInterface::class);

        $clientMock = get_class($client) === 'Algolia\AlgoliaSearch\Client' ? mock(Client::class) : $client;
        $clientMock->shouldReceive('initIndex')->zeroOrMoreTimes()->with($indexName)->andReturn($indexMock);

        $engineMock = mock(AlgoliaEngine::class, [$clientMock])->makePartial();
        $managerMock = mock(EngineManager::class)->makePartial();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(ClientInterface::class, $clientMock);
        $this->swap(EngineManager::class, $managerMock);

        return $indexMock;
    }
}
