<?php

declare(strict_types=1);

/**
 * This file is part of Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\ScoutExtended;

use ReflectionClass;
use Laravel\Scout\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\AnalyticsClient;
use Algolia\ScoutExtended\Engines\AlgoliaEngine;
use Algolia\ScoutExtended\Managers\EngineManager;
use Algolia\ScoutExtended\Console\Commands\SyncCommand;
use Algolia\ScoutExtended\Console\Commands\FlushCommand;
use Algolia\ScoutExtended\Searchable\AggregatorObserver;
use Algolia\ScoutExtended\Console\Commands\ImportCommand;
use Algolia\ScoutExtended\Console\Commands\StatusCommand;
use Algolia\ScoutExtended\Console\Commands\OptimizeCommand;
use Algolia\ScoutExtended\Console\Commands\ReImportCommand;
use Algolia\ScoutExtended\Console\Commands\MakeAggregatorCommand;

final class ScoutExtendedServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'algolia');

        Blade::component('algolia::components.scout', 'scout');
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->register(ScoutServiceProvider::class);

        $this->registerBinds();
        $this->registerCommands();
        $this->registerMacros();
    }

    /**
     * Binds Algolia services into the container.
     *
     * @return void
     */
    private function registerBinds(): void
    {
        $this->app->bind(Algolia::class, function () {
            return new Algolia($this->app);
        });

        $this->app->alias(Algolia::class, 'algolia');

        $this->app->singleton(EngineManager::class, function ($app) {
            return new EngineManager($app);
        });

        $this->app->alias(EngineManager::class, \Laravel\Scout\EngineManager::class);

        $this->app->bind(AlgoliaEngine::class, function (): AlgoliaEngine {
            return $this->app->make(\Laravel\Scout\EngineManager::class)->createAlgoliaDriver();
        });

        $this->app->alias(AlgoliaEngine::class, 'algolia.engine');
        $this->app->bind(SearchClient::class, function (): SearchClient {
            return $this->app->make('algolia.engine')->getClient();
        });

        $this->app->alias(SearchClient::class, 'algolia.client');

        $this->app->bind(AnalyticsClient::class, function (): AnalyticsClient {
            return AnalyticsClient::create(config('scout.algolia.id'), config('scout.algolia.secret'));
        });

        $this->app->alias(AnalyticsClient::class, 'algolia.analytics');

        $this->app->singleton(AggregatorObserver::class, AggregatorObserver::class);
    }

    /**
     * Register artisan commands.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeAggregatorCommand::class,
                ImportCommand::class,
                FlushCommand::class,
                OptimizeCommand::class,
                ReImportCommand::class,
                StatusCommand::class,
                SyncCommand::class,
            ]);
        }
    }

    /**
     * Register macros.
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    private function registerMacros(): void
    {
        Builder::mixin(new BuilderMacros);
    }
}
