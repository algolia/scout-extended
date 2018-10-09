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
use Illuminate\Database\Eloquent\SoftDeletes;
use Algolia\ScoutExtended\Contracts\SearchableCountableContract;

/**
 * @internal
 */
final class RecordsCounter
{
    /**
     * Get the number of local searchable records of
     * the given searchable class.
     *
     * @param  string $searchable
     *
     * @return int
     */
    public function local(string $searchable): int
    {
        if (($instance = new $searchable) instanceof SearchableCountableContract) {
            $count = $instance->getSearchableCount();
        } else {
            $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($searchable), true) && config('scout.soft_delete', false);

            $count = $searchable::query()->when($softDeletes, function ($query) {
                $query->withTrashed();
            })->count();
        }

        return (int) $count;
    }

    /**
     * Get the number of remote searchable records of
     * the given searchable class.
     *
     * @param  string $searchable
     *
     * @return int
     */
    public function remote(string $searchable): int
    {
        return (int) $searchable::search()->count();
    }
}
