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
use Laravel\Scout\EngineManager;
use Algolia\AlgoliaSearch\Analytics;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Laravel\Scout\Engines\AlgoliaEngine;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\ScoutExtended\Console\Commands\SyncCommand;
use Algolia\ScoutExtended\Console\Commands\FlushCommand;
use Algolia\ScoutExtended\Searchable\AggregatorObserver;
use Algolia\ScoutExtended\Console\Commands\ImportCommand;
use Algolia\ScoutExtended\Console\Commands\StatusCommand;
use Algolia\ScoutExtended\Console\Commands\OptimizeCommand;
use Algolia\ScoutExtended\Console\Commands\ReImportCommand;
use Algolia\ScoutExtended\Console\Commands\AggregatorCommand;

final class ScoutExtendedServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'algolia');

        Blade::component('algolia::components.scout', 'scout');
        Blade::component('algolia::components.results', 'results');
        Blade::directive('search', function () {
            return '<ais-search-box placeholder="Find products..."></ais-search-box>';
        });
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

        $this->app->bind(AlgoliaEngine::class, function (): AlgoliaEngine {
            return $this->app->make(EngineManager::class)->createAlgoliaDriver();
        });

        $this->app->alias(AlgoliaEngine::class, 'algolia.engine');

        $this->app->bind(ClientInterface::class, function (): ClientInterface {
            $engine = $this->app->make('algolia.engine');
            $reflection = new ReflectionClass(AlgoliaEngine::class);
            $property = $reflection->getProperty('algolia');
            $property->setAccessible(true);

            return $property->getValue($engine);
        });

        $this->app->alias(ClientInterface::class, 'algolia.client');

        $this->app->bind(Analytics::class, function (): Analytics {
            return Analytics::create(config('scout.algolia.id'), config('scout.algolia.secret'));
        });

        $this->app->alias(Analytics::class, 'algolia.analytics');

        $this->app->singleton(AggregatorObserver::class, AggregatorObserver::class);
        // $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
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
                AggregatorCommand::class,
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
     */
    private function registerMacros(): void
    {
        Builder::mixin(new BuilderMacros);
    }
}
