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

namespace Algolia\ScoutExtended\Settings\SettingAttribute;

use Illuminate\Support\Str;
use Algolia\ScoutExtended\Contracts\SettingContract;

class FacetingAttribute implements SettingContract
{
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
     * Checks if the given key/value is a 'attributesForFaceting'.
     *
     * @param  string $key
     * @param  mixed $value
     * @param array $attributesForFaceting
     *
     * @return array
     */
    public static function exist(string $key, $value, array $attributesForFaceting): array
    {
        if (Str::is(self::$attributesForFacetingKeys, $key)) {
            $attributesForFaceting[] = $key;

            return $attributesForFaceting;
        }

        return $attributesForFaceting;
    }
}