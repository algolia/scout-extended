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
     * @var array
     */
    private $defaults;

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
     * @param  \Algolia\AlgoliaSearch\Interfaces\IndexInterface $index
     *
     * @return array
     */
    public function from(IndexInterface $index): array
    {
        return $this->getSettings($index);
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
            $index->saveObject(['objectID' => 'temp']);
            if (! (defined('SCOUT_EXTENDED_PHPUNIT_IS_RUNNING') && SCOUT_EXTENDED_PHPUNIT_IS_RUNNING)) {
                sleep(1);
            }
            $settings = $this->getSettings($index);
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
}
