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
use function func_num_args;
use Laravel\Scout\Builder as BaseBuilder;

final class Builder extends BaseBuilder
{
    /**
     * Customize the search to be around a given location.
     *
     * @link https://www.algolia.com/doc/guides/geo-search/geo-search-overview
     *
     * @param float $lat Latitude of the center
     * @param float $lng Longitude of the center
     *
     * @return $this
     */
    public function aroundLatLng(float $lat, float $lng): self
    {
        return $this->with([
            'aroundLatLng' => $lat.','.$lng,
        ]);
    }

    /**
     * Count the number of items in the search results.
     *
     * @return int
     */
    public function count(): int
    {
        $raw = $this->raw();

        return array_key_exists('nbHits', $raw) ? (int) $raw['nbHits'] : 0;
    }

    /**
     * Customize the search adding a where clause.
     *
     * @param  string $field
     * @param  string $operator
     * @param  mixed $value
     *
     * @return $this
     */
    public function where($field, $operator = null, $value = null): self
    {
        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        if (func_num_args() === 2) {
            return parent::where($field, $operator);
        }

        return parent::where($field, "$operator $value");
    }

    /**
     * Customize the search with the provided search parameters.
     *
     * @link https://www.algolia.com/doc/api-reference/search-api-parameters
     *
     * @param array $parameters The search parameters.
     *
     * @return $this
     */
    public function with(array $parameters): self
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
}
