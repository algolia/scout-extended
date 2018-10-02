<?php

declare(strict_types=1);

namespace Tests;

use function get_class;
use Mockery\MockInterface;
use Algolia\AlgoliaSearch\Index;
use Laravel\Scout\EngineManager;
use Algolia\AlgoliaSearch\Client;
use Laravel\Scout\Engines\AlgoliaEngine;
use Algolia\ScoutExtended\Settings\Compiler;
use Orchestra\Testbench\TestCase as BaseTestCase;
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
        @unlink(__DIR__.'/laravel/config/scout-users.php');

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Laravel\Scout\ScoutServiceProvider::class,
            \Algolia\ScoutExtended\ScoutExtendedServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Algolia' => \Algolia\ScoutExtended\Facades\Algolia::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
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
                'views_count',
                'category_type',
            ],
            'customRanking' => ['desc(views_count)'],
            'attributesForFaceting' => ['category_type'],
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

    protected function assertSettingsSet($indexMock, array $settings)
    {
        $responseMock = mock(\Algolia\AlgoliaSearch\Response\AbstractResponse::class);

        $responseMock->shouldReceive('wait')->zeroOrMoreTimes();

        $indexMock->shouldReceive('setSettings')->zeroOrMoreTimes()->with($settings)->andReturn($responseMock);
    }
}
