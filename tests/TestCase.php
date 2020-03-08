<?php

declare(strict_types=1);

namespace Tests;

use Algolia\AlgoliaSearch\Response\AbstractResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\ScoutExtended\Engines\AlgoliaEngine;
use Algolia\ScoutExtended\Facades\Algolia as AlgoliaFacade;
use Algolia\ScoutExtended\Managers\EngineManager;
use Algolia\ScoutExtended\ScoutExtendedServiceProvider;
use Algolia\ScoutExtended\Settings\Compiler;
use function get_class;
use Illuminate\Support\Facades\Artisan;
use Laravel\Scout\ScoutServiceProvider;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->setBasePath(__DIR__.'/laravel');

        $this->withFactories(database_path('factories'));
        Artisan::call('migrate:fresh', ['--database' => 'testbench']);
        @unlink(config_path('scout-users.php'));
    }

    public function tearDown(): void
    {
        @unlink(__DIR__.'/laravel/config/scout-users.php');

        Mockery::close();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            ScoutServiceProvider::class,
            ScoutExtendedServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Algolia' => AlgoliaFacade::class,
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
        $index = $this->mockIndex('temp-laravel-scout-extended', $defaults = require __DIR__.'/resources/defaults.php');
        $index->shouldReceive('delete')->zeroOrMoreTimes();

        return $defaults;
    }

    protected function assertLocalHas(array $settings, string $settingsPath = null): void
    {
        if ($settingsPath === null) {
            $settingsPath = config_path('scout-users.php');
        }

        $this->assertFileExists($settingsPath);
        $this->assertEquals($settings, require $settingsPath);
    }

    protected function local(): array
    {
        $viewVariables = array_fill_keys(Compiler::getViewVariables(), null);

        return array_merge($viewVariables, [
            'searchableAttributes' => [
                'name',
                'email',
                'category_type',
            ],
            'customRanking' => [
                'desc(email_verified_at)',
                'desc(created_at)',
                'desc(updated_at)',
                'desc(views_count)',
            ],
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
        $engineMock = mock(app(AlgoliaEngine::class))->makePartial()->shouldIgnoreMissing();

        $managerMock = mock(EngineManager::class)->makePartial()->shouldIgnoreMissing();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(EngineManager::class, $managerMock);

        return $engineMock;
    }

    protected function mockClient(): MockInterface
    {
        $client = $this->app->get(SearchClient::class);

        $clientMock = get_class($client) === SearchClient::class ? mock(SearchClient::class) : $client;

        $this->swap(SearchClient::class, $clientMock);

        return $clientMock;
    }

    protected function mockIndex(string $model, array $settings = [], array $userData = null): MockInterface
    {
        $indexMock = mock(SearchIndex::class);
        $indexName = class_exists($model) ? (new $model)->searchableAs() : $model;
        $indexMock->shouldReceive('getIndexName')->zeroOrMoreTimes()->andReturn($indexName);

        $indexMock->shouldReceive('getSettings')->zeroOrMoreTimes()->andReturn(array_merge($settings, [
            'userData' => @json_encode($userData),
        ]));

        $clientMock = $this->mockClient();
        $clientMock->shouldReceive('initIndex')->zeroOrMoreTimes()->with($indexName)->andReturn($indexMock);

        $algoliaEngine = app(AlgoliaEngine::class);

        $algoliaEngine->setClient($clientMock);

        $engineMock = mock($algoliaEngine)->makePartial();

        $managerMock = mock(EngineManager::class)->makePartial();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(EngineManager::class, $managerMock);

        return $indexMock;
    }

    protected function assertSettingsSet($indexMock, array $settings, array $userData = null): void
    {
        if (! empty($settings)) {
            $indexMock->shouldReceive('setSettings')->once()->with($settings)->andReturn($this->mockResponse());
        }

        if (! empty($userData)) {
            $indexMock->shouldReceive('setSettings')->once()->with(['userData' => @json_encode($userData)])
                ->andReturn($this->mockResponse());
        }
    }

    protected function mockResponse(): MockInterface
    {
        $responseMock = mock(AbstractResponse::class);

        $responseMock->shouldReceive('wait')->zeroOrMoreTimes();

        return $responseMock;
    }
}
