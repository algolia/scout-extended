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

class UnretrievableAttribute implements SettingContract
{
    /**
     * @var string[]
     */
    private static $unretrievableAttributesKeys = [
        '*password*',
        '*token*',
        '*secret*',
        '*hash*',
    ];

    /**
     * Checks if the given key/value is a 'UnretrieableAttribute'.
     *
     * @param  string $key
     * @param  mixed $value
     * @param array $unretrievableAttributes
     *
     * @return array
     */
    public static function exist(string $key, $value, array $unretrievableAttributes): array
    {
        if (is_string($key) && Str::is(self::$unretrievableAttributesKeys, $key)) {
            $unretrievableAttributes[] = $key;

            return $unretrievableAttributes;
        }

        return $unretrievableAttributes;
    }
}
