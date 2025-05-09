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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Support\Collection;

/**
 * @method static void searchable()
 */
class AggregatorCollection extends Collection
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * The class name of the aggregator.
     *
     * @var string|null
     */
    public $aggregator;

    /**
     * Make all the models in this collection unsearchable.
     *
     * @return void
     */
    public function unsearchable(): void
    {
        $aggregator = get_class($this->first());

        (new $aggregator)->queueRemoveFromSearch($this);
    }

    /**
     * Prepare the instance for serialization.
     *
     * @return string[]
     */
    public function __sleep()
    {
        $this->aggregator = get_class($this->first());

        $this->items = $this->getSerializedPropertyValue(EloquentCollection::make($this->map(function ($aggregator) {
            return $aggregator->getModel();
        })));

        return ['aggregator', 'items'];
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->items = $this->getRestoredPropertyValue($this->items)->map(function ($model) {
            return $this->aggregator::create($model);
        })->toArray();
    }
}
