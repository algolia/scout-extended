<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\LaravelScoutExtended;

use ReflectionClass;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Algolia\AlgoliaSearch\Analytics;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Engines\AlgoliaEngine;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\LaravelScoutExtended\Settings\Synchronizer;
use Algolia\LaravelScoutExtended\Console\Commands\SyncCommand;
use Algolia\LaravelScoutExtended\Console\Commands\ClearCommand;
use Algolia\LaravelScoutExtended\Console\Commands\OptimizeCommand;

final class LaravelScoutExtendedServiceProvider extends ServiceProvider
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

        $this->app->bind(SynchronizerContract::class, Synchronizer::class);

        Builder::mixin(new BuilderMacros);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearCommand::class,
                SyncCommand::class,
                OptimizeCommand::class,
            ]);
        }
    }
}
