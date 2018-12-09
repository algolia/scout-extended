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
     * Get a set of models from the provided ids.
     *
     * @param \Laravel\Scout\Builder $builder
     * @param  string|object $searchable
     * @param  array $ids
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function from(Builder $builder, $searchable, array $ids): Collection
    {
        $instances = collect();

        foreach ($ids as $id) {
            $modelClass = ObjectIdEncrypter::decryptSearchable($id);
            $modelKey = ObjectIdEncrypter::decryptSearchableKey($id);

            $model = new $modelClass;

            if (in_array(Searchable::class, class_uses_recursive($model), true)) {
                if (! empty($models = $model->getScoutModelsByIds($builder, [$modelKey]))) {
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
                if ($instance = $query->where($scoutKey, $modelKey)->get()->first()) {
                    $instances->push($instance);
                }
            }
        }

        return $searchable->newCollection($instances->values()->all());
    }
}
