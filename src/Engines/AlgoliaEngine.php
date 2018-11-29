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
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\ScoutExtended\Jobs\DeleteJob;
use Algolia\ScoutExtended\Jobs\UpdateJob;
use Illuminate\Database\Eloquent\Collection;
use Algolia\ScoutExtended\Searchable\ModelsResolver;
use Laravel\Scout\Engines\AlgoliaEngine as BaseAlgoliaEngine;

class AlgoliaEngine extends BaseAlgoliaEngine
{
    /**
     * The Algolia client.
     *
     * @var \Algolia\AlgoliaSearch\SearchClient
     */
    protected $algolia;

    /**
     * Create a new engine instance.
     *
     * @param  \Algolia\AlgoliaSearch\SearchClient $algolia
     * @return void
     */
    public function __construct(SearchClient $algolia)
    {
        $this->algolia = $algolia;
    }

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $algolia
     *
     * @return void
     */
    public function setClient($algolia): void
    {
        $this->algolia = $algolia;
    }

    /**
     * Get the client.
     *
     * @return \Algolia\AlgoliaSearch\SearchClient $algolia
     */
    public function getClient(): SearchClient
    {
        return $this->algolia;
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

    /**
     * {@inheritdoc}
     */
    public function flush($model)
    {
        $index = $this->algolia->initIndex($model->searchableAs());

        $index->clearObjects();
    }

    /**
     * {@inheritdoc}
     */
    protected function filters(Builder $builder): array
    {
        $operators = ['<', '<=', '=', '!=', '>=', '>', ':'];
        
        return collect($builder->wheres)->map(function ($value, $key) use ($operators) {
            if (ends_with($key, $operators) || starts_with($value, $operators)) {
                return $key.' '.$value;
            }

            return $key.'='.$value;
        })->values()->all();
    }
}
