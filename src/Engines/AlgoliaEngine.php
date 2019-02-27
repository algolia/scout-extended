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

use function is_array;
use Laravel\Scout\Builder;
use Illuminate\Support\Str;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\ScoutExtended\Jobs\DeleteJob;
use Algolia\ScoutExtended\Jobs\UpdateJob;
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
            return $searchable->newCollection();
        }

        return app(ModelsResolver::class)->from($builder, $searchable, $results);
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
            if (! is_array($value)) {
                if (Str::endsWith($key, $operators) || Str::startsWith($value, $operators)) {
                    return $key.' '.$value;
                }

                return $key.'='.$value;
            }

            return $value;
        })->values()->all();
    }
}
