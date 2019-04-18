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
use Illuminate\Support\Str;
use Algolia\AlgoliaSearch\SearchIndex;
use Illuminate\Database\QueryException;
use Algolia\ScoutExtended\Searchable\Aggregator;
use Algolia\ScoutExtended\Contracts\SettingsUpdaterContract;
use Algolia\ScoutExtended\Exceptions\ModelNotFoundException;
use Algolia\ScoutExtended\Repositories\RemoteSettingsRepository;
use Algolia\ScoutExtended\Settings\SettingAttribute\searchableAttribute;
use Algolia\ScoutExtended\Settings\SettingAttribute\attributeForFaceting;
use Algolia\ScoutExtended\Settings\SettingAttribute\CustomRankingAttribute;
use Algolia\ScoutExtended\Settings\SettingAttribute\UnretrievableAttribute;
use Algolia\ScoutExtended\Settings\SettingAttribute\DisableTypoToleranceAttribute;
use Illuminate\Database\Eloquent\ModelNotFoundException as BaseModelNotFoundException;

/**
 * @internal
 */
final class LocalFactory
{
    /**
     * @var \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository
     */
    private $remoteRepository;

    /**
     * @var string[]
     */
    private static $settings = [
        'searchableAttributes' => searchableAttribute::class,
        'attributesForFaceting' => attributeForFaceting::class,
        'customRanking' => CustomRankingAttribute::class,
        'disableTypoToleranceOnAttributes' => DisableTypoToleranceAttribute::class,
        'unretrievableAttributes' => UnretrievableAttribute::class,
    ];

    /**
     * SettingsFactory constructor.
     *
     * @param \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository $remoteRepository
     *
     * @return void
     */
    public function __construct(RemoteSettingsRepository $remoteRepository)
    {
        $this->remoteRepository = $remoteRepository;
    }

    /**
     * Creates settings for the given model.
     *
     * @param \Algolia\AlgoliaSearch\SearchIndex $index
     * @param string $model
     *
     * @return \Algolia\ScoutExtended\Settings\Settings
     */
    public function create(SearchIndex $index, string $model): Settings
    {
        $attributes = $this->getAttributes($model);

        $attributeArray = array_fill_keys(array_keys(self::$settings), []);

        foreach ($attributes as $key => $value) {
            $key = (string) $key;
            foreach (self::$settings as $setting => $settingClass) {
                $attributeArray[$setting] = (new $settingClass)->getValue($key, $value, $attributeArray[$setting]);
            }
        }
        foreach ($attributeArray as $key => $value) {
            $detectedSettings[$key] = ! empty($value) ? $value : null;
        }
        $detectedSettings['queryLanguages'] = array_unique([config('app.locale'), config('app.fallback_locale')]);

        $settings = array_merge($this->remoteRepository->find($index)->compiled(), $detectedSettings);
        foreach ($attributes as $key => $value) {
            $method = 'split'.Str::camel((string) $key);
            if (method_exists($model, $method)) {
                $result = (new $model)->{$method}($value);
                if (is_string($result)) {
                    $result = app($result);
                }
                if ($result instanceof SettingsUpdaterContract) {
                    $settings = $result->updateSettings($settings, (string) $key);
                }
            }
        }

        return new Settings($settings, $this->remoteRepository->defaults());
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
                $instance = $searchable::firstOrFail();
            } catch (QueryException | BaseModelNotFoundException $e) {
                throw tap(new ModelNotFoundException())->setModel($searchable);
            }

            $attributes = method_exists($instance, 'toSearchableArray') ? $instance->toSearchableArray() :
                $instance->toArray();
        }

        return $attributes;
    }
}
