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

namespace Algolia\ScoutExtended\Search;

use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Algolia\ScoutExtended\Exceptions\ModelNotDefinedInAggregatorException;

abstract class Aggregator
{
    use Searchable;

    /**
     * The names of the models that should be aggregated.
     *
     * @var string[]
     */
    protected $models = [];

    /**
     * The current model instance, if any.
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected $model;

    /**
     * Returns the index name.
     *
     * @var string
     */
    protected $indexName;

    /**
     * Boot the aggregator.
     *
     * @return void
     */
    public static function bootSearchable(): void
    {
        ($self = new static)->registerSearchableMacros();

        $observer = tap(resolve(Observer::class))->setAggregator(static::class, $models = $self->getModels());

        foreach ($models as $model) {
            \Illuminate\Database\Eloquent\Builder::macro('getScoutKey', function () {
                return UuidGenerator::getUuid($this->model).'_'.$this->model->getKey();
            });

            $model::observe($observer);
        }
    }

    /**
     * Get the names of the models that should be aggregated.
     *
     * @return string[]
     */
    public function getModels(): array
    {
        return $this->models;
    }

    /**
     * Sets the current model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Algolia\ScoutExtended\Aggregator
     */
    public function searchableWith(Model $model): Aggregator
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the requested models from an array of object IDs.
     *
     * @param  \Laravel\Scout\Builder $builder
     * @param  array $ids
     *
     * @return \Illuminate\Support\Collection
     */
    public function getScoutModelsByIds(Builder $builder, array $ids): Collection
    {
        return resolve(ModelsResolver::class)->from($builder, $this->models, $ids);
    }

    /**
     * Get the value used to index the model.
     *
     * @return string
     */
    public function getScoutKey(): string
    {
        $scoutKey = method_exists($this->model, 'getScoutKey') ? $this->model->getScoutKey() : $this->model->getKey();

        if ($this->model === null) {
            throw new ModelNotDefinedInAggregatorException();
        }

        return UuidGenerator::getUuid($this->model).'_'.$scoutKey;
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return config('scout.prefix').UuidGenerator::getUuid(static::class);
    }

    /**
     * Get the searchable array of the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        if ($this->model === null) {
            throw new ModelNotDefinedException();
        }

        return method_exists($this->model, 'toSearchableArray') ? $this->model->toSearchableArray() : $this->model->toArray();
    }
}
