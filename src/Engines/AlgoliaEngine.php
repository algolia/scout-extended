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

namespace Algolia\ScoutExtended\Engines;

use Laravel\Scout\Builder;
use Illuminate\Support\Collection;
use Laravel\Scout\Engines\AlgoliaEngine as BaseAlgoliaEngine;

class AlgoliaEngine extends BaseAlgoliaEngine
{
    /**
     * {@inheritdoc}
     */
    public function map(Builder $builder, $results, $model): Collection
    {
        if (count($results['hits']) === 0) {
            return collect();
        }

        $models = $model->getScoutModelsByIds(
            $builder, collect($results['hits'])->pluck('objectID')->values()->all()
        )->keyBy(function ($model) {
            return $model->getScoutKey();
        })->map->getModel();

        return collect($results['hits'])->map(function ($hit) use ($models) {
            if (isset($models[$hit['objectID']])) {
                return $models[$hit['objectID']];
            }
        })->filter()->values();
    }
}
