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

use function get_class;
use Laravel\Scout\ModelObserver as BaseModelObserver;

final class AggregatorObserver extends BaseModelObserver
{
    /**
     * @var array [
     *      '\App\Post' => [
     *           '\App\Search\NewsAggregator',
     *           '\App\Search\BlogAggregator',
     *       ]
     * ]
     */
    private $aggregators = [];

    /**
     * Set the aggregator.
     *
     * @param string $aggregator
     * @param string[] $models
     *
     * @return void
     */
    public function setAggregator(string $aggregator, array $models): void
    {
        foreach ($models as $model) {
            if (! array_key_exists($model, $this->aggregators)) {
                $this->aggregators[$model] = [];
            }

            $this->aggregators[$model][] = $aggregator;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saved($model): void
    {
        $class = get_class($model);

        if (! array_key_exists($class, $this->aggregators)) {
            return;
        }

        foreach ($this->aggregators[$class] as $aggregator) {
            parent::saved($aggregator::create($model));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleted($model): void
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        if ($this->usesSoftDelete($model) && config('scout.soft_delete', false)) {
            $this->saved($model);
        } else {
            $class = get_class($model);

            if (! array_key_exists($class, $this->aggregators)) {
                return;
            }

            foreach ($this->aggregators[$class] as $aggregator) {
                $aggregator::create($model)->unsearchable();
            }
        }
    }

    /**
     * Handle the force deleted event for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function forceDeleted($model): void
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        $class = get_class($model);

        if (! array_key_exists($class, $this->aggregators)) {
            return;
        }

        foreach ($this->aggregators[$class] as $aggregator) {
            $aggregator::create($model)->unsearchable();
        }
    }
}
