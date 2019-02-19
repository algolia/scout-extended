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
use Laravel\Scout\Builder;
use Illuminate\Support\Arr;
use function call_user_func;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @internal
 */
final class ModelsResolver
{
    /**
     * If the following metadata keys are present in the algolia result,
     * it will be made available with the resolved model.
     *
     */
    const METADATA = ['_highlightResult', '_rankingInfo'];

    /**
     * Get a set of models from the provided results.
     *
     * @param \Laravel\Scout\Builder $builder
     * @param  string|object $searchable
     * @param  array $results
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function from(Builder $builder, $searchable, array $results): Collection
    {
        $instances = collect();
        $hits = collect($results['hits']);

        $models = [];
        $ids = $hits->pluck('objectID')->values()->all();
        foreach ($ids as $id) {
            $modelClass = ObjectIdEncrypter::decryptSearchable($id);
            $modelKey = ObjectIdEncrypter::decryptSearchableKey($id);
            if (! array_key_exists($modelClass, $models)) {
                $models[$modelClass] = [];
            }

            $models[$modelClass][] = $modelKey;
        }

        foreach ($models as $modelClass => $modelKeys) {
            $model = new $modelClass;

            if (in_array(Searchable::class, class_uses_recursive($model), true)) {
                if (! empty($models = $model->getScoutModelsByIds($builder, $modelKeys))) {
                    $instances = $instances->merge($models);
                }
            } else {
                $query = in_array(SoftDeletes::class, class_uses_recursive($model),
                    true) ? $model->withTrashed() : $model->newQuery();

                if ($builder->queryCallback) {
                    call_user_func($builder->queryCallback, $query);
                }

                $scoutKey = method_exists($model,
                    'getScoutKeyName') ? $model->getScoutKeyName() : $model->getQualifiedKeyName();
                if ($models = $query->whereIn($scoutKey, $modelKeys)->get()) {
                    $instances = $instances->merge($models);
                }
            }
        }

        $result = $searchable->newCollection();
        $hits = $hits->keyBy('objectID');

        foreach ($ids as $id) {
            foreach ($instances as $instance) {
                if (($instanceKey = ObjectIdEncrypter::encrypt($instance)) === ObjectIdEncrypter::withoutPart($id)) {
                    if ($hit = $hits->get($instanceKey)) {
                        foreach (Arr::only($hit, self::METADATA) as $metadataKey => $metadataValue) {
                            $instance->withScoutMetadata($metadataKey, $metadataValue);
                        }
                    }

                    $result->push($instance);
                    break;
                }
            }
        }

        return $result;
    }
}
