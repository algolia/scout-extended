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

namespace Algolia\ScoutExtended\Searchable;

class Aggregators extends Aggregator
{
    /**
     * Boot multiple aggregators.
     *
     * @return void
     */
    public static function bootSearchables(array $searchables): void
    {
        (new static)->registerSearchableMacros();

        $models = [];

        foreach ($searchables as $searchable) {
            foreach ((new $searchable)->getModels() as $model) {
                $models[(string) $model][] = $searchable;
            }
        }

        foreach ($models as $model => $searchables) {
            $observer = tap(app(AggregatorObserver::class))->setAggregators($searchables, $model);

            $model::observe($observer);
        }
    }
}
