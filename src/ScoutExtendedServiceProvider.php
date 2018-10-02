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
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Engines\AlgoliaEngine;
use Algolia\ScoutExtended\Search\Observer;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\ScoutExtended\Console\Commands\SyncCommand;
use Algolia\ScoutExtended\Console\Commands\ClearCommand;
use Algolia\ScoutExtended\Console\Commands\OptimizeCommand;

final class ScoutExtendedServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'algolia');
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
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

        $this->app->singleton(Observer::class, function () {
            return new Observer();
        });
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
                ClearCommand::class,
                SyncCommand::class,
                OptimizeCommand::class,
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
