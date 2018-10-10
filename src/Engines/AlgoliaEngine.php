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

use Algolia\ScoutExtended\Searchable\ModelsResolver;
use Algolia\ScoutExtended\Searchable\ObjectIdEncrypter;
use Laravel\Scout\Builder;
use Algolia\AlgoliaSearch\Client as Algolia;
use Illuminate\Database\Eloquent\Collection;
use Algolia\ScoutExtended\Searchable\ObjectsResolver;
use Laravel\Scout\Engines\AlgoliaEngine as BaseAlgoliaEngine;

class AlgoliaEngine extends BaseAlgoliaEngine
{
    /**
     * @var \Algolia\ScoutExtended\Searchable\ObjectsResolver
     */
    private $objectsResolver;

    /**
     * AlgoliaEngine constructor.
     *
     * @param \Algolia\AlgoliaSearch\Client $algolia
     * @param \Algolia\ScoutExtended\Searchable\ObjectsResolver $objectsResolver
     *
     */
    public function __construct(
        Algolia $algolia,
        ObjectsResolver $objectsResolver
    ) {
        parent::__construct($algolia);

        $this->objectsResolver = $objectsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function update($searchables)
    {
        if ($searchables->isEmpty()) {
            return;
        }

        $index = $this->algolia->initIndex($searchables->first()->searchableAs());

        if ($this->usesSoftDelete($searchables->first()) && config('scout.soft_delete', false)) {
            $searchables->each->pushSoftDeleteMetadata();
        }

        $objects = $this->objectsResolver->toUpdate($searchables);
        $index->saveObjects(collect($objects)->filter()->values()->all());
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $models
     * @return void
     */
    public function delete($models)
    {
        $index = $this->algolia->initIndex($models->first()->searchableAs());

        $index->deleteObjects($models->map(function ($model) {
            return ObjectIdEncrypter::encrypt($model);
        })->values()->all());
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
