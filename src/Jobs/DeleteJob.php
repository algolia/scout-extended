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

namespace Algolia\ScoutExtended\Jobs;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\ScoutExtended\Searchable\ObjectIdEncrypter;
use Illuminate\Support\Collection;

/**
 * @internal
 */
class DeleteJob
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private $searchables;

    /**
     * DeleteJob constructor.
     *
     * @param \Illuminate\Support\Collection $searchables
     *
     * @return void
     */
    public function __construct(Collection $searchables)
    {
        $this->searchables = $searchables;
    }

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $client
     *
     * @return void
     */
    public function handle(SearchClient $client): void
    {
        if ($this->searchables->isEmpty()) {
            return;
        }

        $index = $client->initIndex($this->searchables->first()->searchableAs());

        // First fetch all object IDs by tags.
        $objects = $index->browseObjects([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                $this->searchables->map(function ($searchable) {
                    return ObjectIdEncrypter::encrypt($searchable);
                })->toArray(),
            ],
        ]);

        // The ObjectIterator will fetch all pages for us automatically.
        $objectIds = [];
        foreach ($objects as $object) {
            if (isset($object['objectID'])) {
                $objectIds[] = $object['objectID'];
            }
        }

        // Then delete the objects using their object IDs.
        $result = $index->deleteObjects($objectIds);

        if (config('scout.synchronous', false)) {
            $result->wait();
        }
    }
}
