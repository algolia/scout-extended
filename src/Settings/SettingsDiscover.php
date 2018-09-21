<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\LaravelScoutExtended\Settings;

use Algolia\AlgoliaSearch\Interfaces\IndexInterface;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;

/**
 * @internal
 */
final class SettingsDiscover
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
     * DefaultSettingsDiscover constructor.
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
        $indexName = 'temp-'.time();

        $index = $this->client->initIndex($indexName);

        $index->saveObject(['objectID' => 'temp']);

        $settings = $this->getSettings($index);

        $this->client->deleteIndex($indexName);

        return $settings;
    }

    /**
     * Get settings from the provided index.
     *
     * @param  \Algolia\AlgoliaSearch\Interfaces\IndexInterface $index
     *
     * @return array
     */
    public function from(IndexInterface $index): array
    {
        try {
            $settings = $index->getSettings();
        } catch (NotFoundException $e) {
            $index->saveObject(['objectID' => 'temp']);
        }

        $settings = $this->getSettings($index);

        $index->clear();

        return $settings;
    }

    /**
     * @param  \Algolia\AlgoliaSearch\Interfaces\IndexInterface $index
     *
     * @return array
     */
    private function getSettings(IndexInterface $index): array
    {
        try {
            $settings = $index->getSettings();
        } catch (NotFoundException $e) {
            sleep(1);

            return $this->getSettings($index);
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
