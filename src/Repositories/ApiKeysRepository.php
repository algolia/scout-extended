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

namespace Algolia\ScoutExtended\Repositories;

use DateInterval;
use function is_string;
use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Contracts\Cache\Repository;

/**
 * @internal
 */
final class ApiKeysRepository
{
    /**
     * Holds the search key.
     */
    private const SEARCH_KEY = 'scout-extended.user-data.search-key';

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    /**
     * @var \Algolia\AlgoliaSearch\SearchClient
     */
    private $client;

    /**
     * ApiKeysRepository constructor.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param \Algolia\AlgoliaSearch\SearchClient $client
     *
     * @return void
     */
    public function __construct(Repository $cache, SearchClient $client)
    {
        $this->cache = $cache;
        $this->client = $client;
    }

    /**
     * @param  string|object $searchable
     *
     * @return string
     */
    public function getSearchKey($searchable): string
    {
        $searchable = is_string($searchable) ? new $searchable : $searchable;

        $searchableAs = $searchable->searchableAs();

        $securedSearchKey = $this->cache->get(self::SEARCH_KEY.'.'.$searchableAs);

        if ($securedSearchKey === null) {
            $id = config('app.name').'::searchKey';

            $keys = $this->client->listApiKeys()['keys'];

            $searchKey = null;

            foreach ($keys as $key) {
                if (array_key_exists('description', $key) && $key['description'] === $id) {
                    $searchKey = $key['value'];
                }
            }

            $searchKey = $searchKey ?? $this->client->addApiKey(['search'], [
                'description' => config('app.name').'::searchKey',
            ])->getBody()['key'];

            // Key will be valid for 25 hours.
            $validUntil = time() + (3600 * 25);

            $securedSearchKey = $this->client::generateSecuredApiKey($searchKey, [
                'restrictIndices' => $searchableAs,
                'validUntil' => $validUntil,
            ]);

            $this->cache->put(
                self::SEARCH_KEY.'.'.$searchableAs, $securedSearchKey, DateInterval::createFromDateString('24 hours')
            );
        }

        return $securedSearchKey;
    }
}
