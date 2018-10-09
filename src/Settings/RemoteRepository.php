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

namespace Algolia\ScoutExtended\Settings;

use Algolia\AlgoliaSearch\Index;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;

/**
 * @internal
 */
final class RemoteRepository
{
    /**
     * Settings that may be know by other names.
     *
     * @var array
     */
    private static $aliases = [
        'attributesToIndex' => 'searchableAttributes',
    ];

    /**
     * @var \Algolia\AlgoliaSearch\Interfaces\ClientInterface
     */
    private $client;

    /**
     * @var array
     */
    private $defaults;

    /**
     * RemoteRepository constructor.
     *
     * @param \Algolia\AlgoliaSearch\Interfaces\ClientInterface $client
     *
     * @return void
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Get the default settings.
     *
     * @return array
     */
    public function defaults(): array
    {
        if ($this->defaults === null) {
            $indexName = 'temp-laravel-scout-extended';
            $index = $this->client->initIndex($indexName);
            $this->defaults = $this->getSettings($index);
            $this->client->deleteIndex($indexName);
        }

        return $this->defaults;
    }

    /**
     * Get settings from the provided index.
     *
     * @param  \Algolia\AlgoliaSearch\Index $index
     *
     * @return array
     */
    public function from(Index $index): array
    {
        return $this->getSettings($index);
    }

    /**
     * @param  \Algolia\AlgoliaSearch\Index $index
     *
     * @return array
     */
    private function getSettings(Index $index): array
    {
        try {
            $settings = $this->attachSynonyms($index, $index->getSettings());
        } catch (NotFoundException $e) {
            $index->saveObject(['objectID' => 'temp'])->wait();
            $settings = $this->attachSynonyms($index, $index->getSettings());
            $index->clear();
        }

        foreach (self::$aliases as $from => $to) {
            if (array_key_exists($from, $settings)) {
                $settings[$to] = $settings[$from];
                unset($settings[$from]);
            }
        }

        return $settings;
    }

    /**
     * @param  \Algolia\AlgoliaSearch\Index $index
     * @param  array $settings
     *
     * @return array
     */
    private function attachSynonyms(Index $index, array $settings): array
    {
        $settings['synonyms'] = [];

        foreach ($index->browseSynonyms() as $key => $synonym) {

            if (isset($synonym['input'])) {
                $settings['synonyms'][$synonym['input']] = $synonym['synonyms'];
            } else {
                $settings['synonyms'][] = $synonym['synonyms'];
            }
        }

        return $settings;
    }
}
