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
use Illuminate\Support\Collection;
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
     * @param  string $searchable
     * @param  array $models
     * @param  array $ids
     *
     * @return \Illuminate\Support\Collection
     */
    public function from(Builder $builder, string $searchable, array $models, array $ids): Collection
    {
        $models = UuidGenerator::keyByUuid($models);

        $instances = collect();

        foreach ($ids as $id) {
            $model = (new $models[ObjectIdEncrypter::decryptSearchableUuid($id)]);
            $modelKey = ObjectIdEncrypter::decryptSearchableKey($id);
            $query = in_array(SoftDeletes::class,
                class_uses_recursive($model)) ? $model->withTrashed() : $model->newQuery();

            if ($builder->queryCallback) {
                call_user_func($builder->queryCallback, $query);
            }

            $scoutKey = method_exists($model,
                'getScoutKeyName') ? $model->getScoutKeyName() : $model->getQualifiedKeyName();

            if ($instance = $query->where($scoutKey, $modelKey)->get()->first()) {
                $instances->push($searchable::create($instance));
            }
        }

        return $instances;
    }
}
