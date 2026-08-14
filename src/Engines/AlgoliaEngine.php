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

namespace Algolia\ScoutExtended\Engines;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\ScoutExtended\Jobs\DeleteJob;
use Algolia\ScoutExtended\Jobs\UpdateJob;
use Algolia\ScoutExtended\Searchable\ModelsResolver;
use Algolia\ScoutExtended\Searchable\ObjectIdEncrypter;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Algolia4Engine;
use function is_array;

class AlgoliaEngine extends Algolia4Engine
{
    /**
     * @param \Algolia\AlgoliaSearch\Api\SearchClient $algolia
     *
     * @return void
     */
    public function setClient($algolia): void
    {
        $this->algolia = $algolia;
    }

    /**
     * Get the client.
     *
     * @return \Algolia\AlgoliaSearch\Api\SearchClient
     */
    public function getClient(): SearchClient
    {
        return $this->algolia;
    }

    /**
     * {@inheritdoc}
     */
    public function update($searchables)
    {
        dispatch_sync(new UpdateJob($searchables));
    }

    /**
     * {@inheritdoc}
     */
    public function delete($searchables)
    {
        dispatch_sync(new DeleteJob($searchables));
    }

    /**
     * {@inheritdoc}
     */
    public function map(Builder $builder, $results, $searchable)
    {
        if (count($results['hits']) === 0) {
            return $searchable->newCollection();
        }

        return app(ModelsResolver::class)->from($builder, $searchable, $results);
    }

    /**
     * {@inheritdoc}
     */
    public function lazyMap(Builder $builder, $results, $searchable)
    {
        return LazyCollection::make($this->map($builder, $results, $searchable));
    }

    /**
     * @return string
     */
    protected function filters(Builder $builder): string
    {
        $parts = [];

        foreach ($builder->whereIns as $field => $values) {
            $parts[] = empty($values)
                ? '(0 = 1)'
                : '('.implode(' OR ', array_map(fn ($value) => $this->formatFilter($field, $value), $values)).')';
        }

        foreach ($builder->whereNotIns as $field => $values) {
            // Algolia's filter grammar cannot negate a parenthesized group:
            // NOT binds to a single clause, so each value is negated individually.
            foreach ($values as $value) {
                $parts[] = 'NOT '.$this->formatFilter($field, $value);
            }
        }

        foreach ($builder->wheres as ['field' => $field, 'operator' => $operator, 'value' => $value]) {
            $parts[] = match ($operator) {
                ':' => "$field: {$value[0]} TO {$value[1]}",
                '=' => $this->formatFilter($field, $value),
                '!=' => $this->formatNegatedFilter($field, $value),
                default => "$field $operator $value",
            };
        }

        return implode(' AND ', $parts);
    }

    /**
     * Format an equality filter in Algolia's filter syntax.
     *
     * Algolia reserves "=" for numeric comparisons, so strings and booleans
     * must use the facet form "field:value", with string values quoted and
     * escaped. Numeric values (including numeric strings, which Algolia
     * compares numerically) keep the "=" operator.
     */
    private function formatFilter(string $field, mixed $value): string
    {
        if (is_bool($value)) {
            return $field.':'.($value ? 'true' : 'false');
        }

        if ($this->isNonNumericString($value)) {
            return $field.":'".str_replace(['\\', "'"], ['\\\\', "\\'"], $value)."'";
        }

        return "$field=$value";
    }

    /**
     * Format a negated equality filter, keeping Algolia's native "!="
     * operator for numeric values.
     */
    private function formatNegatedFilter(string $field, mixed $value): string
    {
        if (is_bool($value) || $this->isNonNumericString($value)) {
            return 'NOT '.$this->formatFilter($field, $value);
        }

        return "$field != $value";
    }

    private function isNonNumericString(mixed $value): bool
    {
        return is_string($value) && ! is_numeric($value);
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['hits'])->pluck('objectID')->values()
            ->map([ObjectIdEncrypter::class, 'decryptSearchableKey']);
    }
}
