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
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * @method static string searchable()
 * @method static string unsearchable()
 */
final class AggregatorCollection extends Collection
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * The class name of the aggregator.
     *
     * @var string|null
     */
    public $aggregator;

    /**
     * Prepare the instance for serialization.
     *
     * @return array []string
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
