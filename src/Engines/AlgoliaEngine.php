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
use Algolia\AlgoliaSearch\Client;
use Algolia\ScoutExtended\Jobs\DeleteJob;
use Algolia\ScoutExtended\Jobs\UpdateJob;
use Illuminate\Database\Eloquent\Collection;
use Algolia\ScoutExtended\Searchable\ModelsResolver;
use Laravel\Scout\Engines\AlgoliaEngine as BaseAlgoliaEngine;

class AlgoliaEngine extends BaseAlgoliaEngine
{
    /**
     * @param \Algolia\AlgoliaSearch\Client $algolia
     *
     * @return void
     */
    public function setClient(Client $algolia): void
    {
        $this->algolia = $algolia;
    }

    /**
     * {@inheritdoc}
     */
    public function update($searchables)
    {
        dispatch_now(new UpdateJob($searchables));
    }

    /**
     * {@inheritdoc}
     */
    public function delete($searchables)
    {
        dispatch_now(new DeleteJob($searchables));
    }

    /**
     * {@inheritdoc}
     */
    public function map(Builder $builder, $results, $searchable)
    {
        if (count($results['hits']) === 0) {
            return Collection::make();
        }

        $ids = collect($results['hits'])->pluck('objectID')->values()->all();

        return resolve(ModelsResolver::class)->from($builder, $searchable, $ids);
    }
}
