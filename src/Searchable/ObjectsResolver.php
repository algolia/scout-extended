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

use Illuminate\Support\Collection;

/**
 * @internal
 */
final class ObjectsResolver
{
    /**
     * Get an collection of objects to update
     * from the given searchables.
     *
     * @param \Illuminate\Support\Collection $searchables
     *
     * @return \Illuminate\Support\Collection
     */
    public function toUpdate(Collection $searchables): Collection
    {
        $result = [];

        foreach ($searchables as $key => $searchable) {
            if (empty($array = array_merge($searchable->toSearchableArray(), $searchable->scoutMetadata()))) {
                continue;
            }

            $array['objectID'] = $searchable->getScoutKey();

            $result[] = $array;
        }

        return collect($result);
    }
}
