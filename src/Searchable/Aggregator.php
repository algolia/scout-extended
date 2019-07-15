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

use function in_array;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Events\ModelsImported;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Algolia\ScoutExtended\Contracts\SearchableCountableContract;
use Algolia\ScoutExtended\Exceptions\ModelNotDefinedInAggregatorException;

abstract class Aggregator implements SearchableCountableContract
{
    use Searchable;

    /**
     * The names of the models that should be aggregated.
     *
     * @var string[]
     */
    protected $models = [];

    /**
     * The model being queried, if any.
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected $model;

    /**
     * The relationships per model that should be loaded.
     *
     * @var mixed[]
     */
    protected $relations = [];

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

        $observer = tap(app(AggregatorObserver::class))->setAggregator(static::class, $models = $self->getModels());

        foreach ($models as $model) {
            $model::observe($observer);
        }
    }

    /**
     * Creates an instance of the aggregator.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Algolia\ScoutExtended\Searchable\Aggregator
     */
    public static function create(Model $model): Aggregator
    {
        return (new static)->setModel($model);
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
     * Get the model instance being queried.
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Algolia\ScoutExtended\Exceptions\ModelNotDefinedInAggregatorException
     */
    public function getModel(): Model
    {
        if ($this->model === null) {
            throw new ModelNotDefinedInAggregatorException();
        }

        return $this->model;
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Algolia\ScoutExtended\Searchable\Aggregator
     */
    public function setModel(Model $model): Aggregator
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the relations to load.
     *
     * @param  string  $modelClass
     *
     * @return  array
     */
    public function getRelations($modelClass): array
    {
        return $this->relations[$modelClass] ?? [];
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getScoutKey()
    {
        if ($this->model === null) {
            throw new ModelNotDefinedInAggregatorException();
        }

        return method_exists($this->model, 'getScoutKey') ? $this->model->getScoutKey() : $this->model->getKey();
    }

    /**
     * Get the index name for the searchable.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return config('scout.prefix').str_replace('\\', '', Str::snake(class_basename(static::class)));
    }

    /**
     * Get the searchable array of the searchable.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        if ($this->model === null) {
            throw new ModelNotDefinedInAggregatorException();
        }

        return method_exists($this->model, 'toSearchableArray') ? $this->model->toSearchableArray() :
            $this->model->toArray();
    }

    /**
     * Make all instances of the model searchable.
     *
     * @return void
     */
    public static function makeAllSearchable()
    {
        foreach ((new static)->getModels() as $model) {
            $instance = new $model;

            $softDeletes =
                in_array(SoftDeletes::class, class_uses_recursive($model)) && config('scout.soft_delete', false);

            $instance->newQuery()->when($softDeletes, function ($query) {
                $query->withTrashed();
            })->orderBy($instance->getKeyName())->chunk(config('scout.chunk.searchable', 500), function ($models) {
                $models = $models->map(function ($model) {
                    return static::create($model);
                })->filter->shouldBeSearchable();

                $models->searchable();

                event(new ModelsImported($models));
            });
        }
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    public function searchable(): void
    {
        AggregatorCollection::make([$this])->searchable();
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    public function unsearchable(): void
    {
        AggregatorCollection::make([$this])->unsearchable();
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableCount(): int
    {
        $count = 0;

        foreach ($this->getModels() as $model) {
            $softDeletes =
                in_array(SoftDeletes::class, class_uses_recursive($model), true) && config('scout.soft_delete', false);

            $count += $model::query()->when($softDeletes, function ($query) {
                $query->withTrashed();
            })->count();
        }

        return (int) $count;
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $searchables
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $searchables = []): Collection
    {
        return new Collection($searchables);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string $method
     * @param  array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $model = $this->model ?? new class extends Model {
        };

        return $model->$method(...$parameters);
    }
}
