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

namespace Algolia\ScoutExtended;

use function is_callable;
use Laravel\Scout\Builder as BaseBuilder;

final class Builder extends BaseBuilder
{
    /*
     * Count the number of items in the search results.
     *
     * @return int
     */
    public function count(): int
    {
        $raw = $this->raw();

        return array_key_exists('nbHits', $raw) ? (int) $raw['nbHits'] : 0;
    }

    /*
     * Customize the search with the provided search parameters.
     *
     * @link https://www.algolia.com/doc/api-reference/search-api-parameters
     *
     * @param array $parameters The search parameters.
     *
     * @return \Algolia\ScoutExtended\Builder
     */
    public function with(array $parameters): Builder
    {
        $callback = $this->callback;

        $this->callback = function ($algolia, $query, $baseParameters) use ($parameters, $callback) {
            $parameters = array_merge($parameters, $baseParameters);

            if (is_callable($callback)) {
                return $callback($algolia, $query, $parameters);
            }

            return $algolia->search($query, $parameters);
        };

        return $this;
    }

    /*
     * Customize the search to be around a given location.
     *
     * @link https://www.algolia.com/doc/guides/geo-search/geo-search-overview
     *
     * @param float $lat Latitude of the center
     * @param float $lng Longitude of the center
     *
     * @return \Algolia\ScoutExtended\Builder
     */
    public function aroundLatLng(float $lat, float $lng): Builder
    {
        return $this->with([
            'aroundLatLng' => $lat.','.$lng,
        ]);
    }
}
