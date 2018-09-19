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

use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Places;
use Algolia\AlgoliaSearch\Analytics;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Engines\AlgoliaEngine;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;

final class LaravelScoutExtendedServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind('algolia', function () {
            return $this->app->make(Algolia::class);
        });

        $this->app->bind('algolia.engine', function (): AlgoliaEngine {
            return $this->app->make(EngineManager::class)->createAlgoliaDriver();
        });

        $this->app->bind('algolia.client', function (): ClientInterface {
            return Client::create(config('scout.algolia.id'), config('scout.algolia.secret'));
        });

        $this->app->bind('algolia.analytics', function (): Analytics {
            return Analytics::create(config('scout.algolia.id'), config('scout.algolia.secret'));
        });
        
        $this->app->bind('algolia.version', function (): string {
            return \Algolia\AlgoliaSearch\Algolia::VERSION;
        });

        Builder::mixin(new BuilderMacros);
    }
}
