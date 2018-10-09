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
    public function map(Builder $builder, $results, $searchable): Collection
    {
        if (count($results['hits']) === 0) {
            return collect();
        }

        $searchables = $searchable->getScoutModelsByIds(
            $builder, collect($results['hits'])->pluck('objectID')->values()->all()
        )->keyBy(function ($searchable) {
            return $searchable->getScoutKey();
        })->map->getModel();

        return collect($results['hits'])->map(function ($hit) use ($searchables) {
            if (isset($searchables[$hit['objectID']])) {
                return $searchables[$hit['objectID']];
            }
        })->filter()->values();
    }
}
