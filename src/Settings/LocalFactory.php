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

use function in_array;
use function is_string;
use InvalidArgumentException;
use Algolia\AlgoliaSearch\Index;
use Illuminate\Database\QueryException;
use Algolia\ScoutExtended\Search\Aggregator;

/**
 * @internal
 */
final class LocalFactory
{
    /**
     * @var \Algolia\ScoutExtended\Settings\RemoteRepository
     */
    private $remoteRepository;

    /**
     * @var string[]
     */
    private static $customRankingKeys = [
        'id',
        'id_*',
        '*_id',
        '*number*',
        '*count*',
    ];

    /**
     * @var string[]
     */
    private static $unsearchableAttributesKeys = [
        '*image*',
        '*url*',
        '*link*',
        '*password*',
        '*token*',
        '*hash*',
    ];

    /**
     * @var string[]
     */
    private static $attributesForFacetingKeys = [
        '*category*',
        '*list*',
        '*country*',
        '*city*',
        '*type*',
    ];

    /**
     * @var string[]
     */
    private static $unretrievableAttributes = [
        '*password*',
        '*token*',
        '*secret*',
    ];

    /**
     * @var string[]
     */
    private static $unsearchableAttributesValues = [
        'http://*',
        'https://*',
    ];

    /**
     * @var string[]
     */
    private static $disableTypoToleranceOnAttributesKeys = [
        'id',
        'id_*',
        '*_id',
        '*code*',
        '*sku*',
        '*reference*',
    ];

    /**
     * SettingsFactory constructor.
     *
     * @param \Algolia\ScoutExtended\Settings\RemoteRepository $remoteRepository
     *
     * @return void
     */
    public function __construct(RemoteRepository $remoteRepository)
    {
        $this->remoteRepository = $remoteRepository;
    }

    /**
     * Creates settings for the given model.
     *
     * @param \Algolia\AlgoliaSearch\Index $index
     * @param string $model
     *
     * @return \Algolia\ScoutExtended\Settings\Settings
     */
    public function create(Index $index, string $model): Settings
    {
        $attributes = $this->getAttributes($model);
        $searchableAttributes = [];
        $attributesForFaceting = [];
        $customRanking = [];
        $disableTypoToleranceOnAttributes = [];
        $unretrievableAttributes = [];
        foreach ($attributes as $key => $value) {

            $key = (string) $key;

            if ($this->isSearchableAttributes($key, $value)) {
                $searchableAttributes[] = $key;
            }

            if ($this->isAttributesForFaceting($key, $value)) {
                $attributesForFaceting[] = $key;
            }

            if ($this->isCustomRanking($key, $value)) {
                $customRanking[] = "desc({$key})";
            }

            if ($this->isDisableTypoToleranceOnAttributes($key, $value)) {
                $disableTypoToleranceOnAttributes[] = $key;
            }

            if ($this->isUnretrievableAttributes($key, $value)) {
                $unretrievableAttributes[] = $key;
            }
        }

        $detectedSettings = [
            'searchableAttributes' => ! empty($searchableAttributes) ? $searchableAttributes : null,
            'attributesForFaceting' => ! empty($attributesForFaceting) ? $attributesForFaceting : null,
            'customRanking' => ! empty($customRanking) ? $customRanking : null,
            'disableTypoToleranceOnAttributes' => ! empty($disableTypoToleranceOnAttributes) ? $disableTypoToleranceOnAttributes : null,
            'unretrievableAttributes' => ! empty($unretrievableAttributes) ? $unretrievableAttributes : null,
            'queryLanguages' => array_unique([config('app.locale'), config('app.fallback_locale')]),
        ];

        $settings = array_merge($this->remoteRepository->from($index), $detectedSettings);

        return new Settings($settings, $this->remoteRepository->defaults());
    }

    /**
     * Checks if the given key/value is a 'searchableAttributes'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isSearchableAttributes(string $key, $value): bool
    {
        return ! str_is(self::$unsearchableAttributesKeys, $key) && ! str_is(self::$unsearchableAttributesValues, $value);
    }

    /**
     * Checks if the given key/value is a 'attributesForFaceting'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isAttributesForFaceting(string $key, $value): bool
    {
        return str_is(self::$attributesForFacetingKeys, $key);
    }

    /**
     * Checks if the given key/value is a 'customRanking'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isCustomRanking(string $key, $value): bool
    {
        return str_is(self::$customRankingKeys, $key);
    }

    /**
     * Checks if the given key/value is a 'disableTypoToleranceOnAttributes'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isDisableTypoToleranceOnAttributes(string $key, $value): bool
    {
        return is_string($key) && str_is(self::$disableTypoToleranceOnAttributesKeys, $key);
    }

    /**
     * Checks if the given key/value is a 'unretrievableAttributes'.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return bool
     */
    public function isUnretrievableAttributes(string $key, $value): bool
    {
        return is_string($key) && str_is(self::$unretrievableAttributes, $key);
    }

    /**
     * Tries to get attributes from the searchable class.
     *
     * @param  string $searchable
     *
     * @return array
     */
    private function getAttributes(string $searchable): array
    {
        $attributes = [];

        if (in_array(Aggregator::class, class_parents($searchable), true)) {
            foreach (($instance = new $searchable)->getModels() as $model) {
                $attributes = array_merge($attributes, $this->getAttributes($model));
            }
        } else {
            $instance = null;

            try {
                $instance = @factory($searchable)->make() ?? $searchable::first();
                $attributes = method_exists($instance, 'toSearchableArray') ? $instance->toSearchableArray() : $instance->toArray();
            } catch (InvalidArgumentException | QueryException $e) {
            }
        }

        return $attributes;
    }
}
