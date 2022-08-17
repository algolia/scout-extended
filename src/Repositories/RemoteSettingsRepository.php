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

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\ScoutExtended\Settings\Settings;

/**
 * @internal
 */
final class RemoteSettingsRepository
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
     * @var \Algolia\AlgoliaSearch\Api\SearchClient
     */
    private $client;

    /**
     * @var array
     */
    private $defaults;

    /**
     * RemoteRepository constructor.
     *
     * @param \Algolia\AlgoliaSearch\Api\SearchClient $client
     *
     * @return void
     */
    public function __construct(SearchClient $client)
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
            $this->defaults = $this->getSettingsRaw($indexName);
            $this->client->deleteIndex($indexName);
        }

        return $this->defaults;
    }

    /**
     * Find the settings of the given Index.
     *
     * @param string $index
     *
     * @return \Algolia\ScoutExtended\Settings\Settings
     */
    public function find($index): Settings
    {
        return new Settings($this->getSettingsRaw($index), $this->defaults());
    }

    /**
     * @param string $index
     * @param \Algolia\ScoutExtended\Settings\Settings $settings
     *
     * @return void
     */
    public function save($index, Settings $settings): void
    {
        $result = $this->client->setSettings($index, $settings->compiled());
        $this->client->waitForTask($index, $result['taskID']);
    }

    /**
     * @param string $index
     *
     * @return array
     */
    public function getSettingsRaw($index): array
    {
        try {
            $settings = $this->client->getSettings($index);
        } catch (NotFoundException $e) {
            $result = $this->client->saveObject($index, ['objectID' => 'temp']);
            $this->client->waitForTask($index, $result['taskID']);
            $settings = $this->client->getSettings($index);

            $this->client->clearObjects($index);
        }

        foreach (self::$aliases as $from => $to) {
            if (array_key_exists($from, $settings)) {
                $settings[$to] = $settings[$from];
                unset($settings[$from]);
            }
        }

        return $settings;
    }
}
