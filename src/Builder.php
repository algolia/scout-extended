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

use Illuminate\Support\Collection;
use function func_num_args;
use Laravel\Scout\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    /**
     * @var Collection
     */
    private $optionalFilters;

    /**
     * {@inheritdoc}
     *
     * @see https://github.com/algolia/scout-extended/issues/98
     */
    public function __construct($model, $query, $callback = null, $softDelete = false)
    {
        parent::__construct($model, (string) $query, $callback, $softDelete);
        $this->optionalFilters = collect();
    }

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
     * @param  mixed $operator
     * @param  mixed $value
     *
     * @return $this
     */
    public function where($field, $operator, $value = null): self
    {
        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        if (func_num_args() === 2) {
            return parent::where($field, $this->transform($operator));
        }

        return parent::where($field, $operator, $this->transform($value));
    }

    /**
     * Customize the search adding a where between clause.
     *
     * Passes an array [min, max] as the where value (Scout v11 normally expects a scalar) so that
     * AlgoliaEngine::filters() can render Algolia's range syntax: "field: min TO max".
     *
     * @param  string $field
     * @param  array $values
     *
     * @return $this
     */
    public function whereBetween($field, array $values): self
    {
        return parent::where($field, ':', [$this->transform($values[0]), $this->transform($values[1])]);
    }

    /**
     * Customize the search adding a where in clause.
     *
     * @param  string $field
     * @param  array $values
     *
     * @return $this
     */
    public function whereIn($field, $values): self
    {
        return parent::whereIn($field, array_map(fn ($v) => $this->transform($v), array_values((array) $values)));
    }

    /**
     * Customize the search adding a where not in clause.
     *
     * @param  string $field
     * @param  array $values
     *
     * @return $this
     */
    public function whereNotIn($field, $values): self
    {
        return parent::whereNotIn($field, array_map(fn ($v) => $this->transform($v), array_values((array) $values)));
    }

    /**
     * Add an optional filter to the search parameters.
     *
     * @link https://www.algolia.com/doc/api-reference/api-parameters/optionalFilters/
     *
     * @param  string $field
     * @param  mixed $value
     *
     * @return  $this
     */
    public function whereOptional($field, $value): self
    {
        $this->optionalFilters->push("$field:$value");
        return $this->with(['optionalFilters' => $this->optionalFilters->join(',')]);
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
        $this->options = array_merge($this->options, $parameters);

        return $this;
    }

    /**
     * Transform the given where value.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    private function transform($value)
    {
        /*
         * Casts carbon instances to timestamp.
         */
        if ($value instanceof \Illuminate\Support\Carbon) {
            $value = $value->getTimestamp();
        }

        return $value;
    }

    /**
     * Get the total number of results from the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    protected function getTotalCount($results)
    {
        return $this->engine()->getTotalCount($results);
    }
}
