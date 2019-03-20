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

class UnsearcheableAttribute implements SettingContract
{
    /**
     * @var string[]
     */
    private static $unsearchableAttributesKeys = [
        'id',
        '*_id',
        'id_*',
        '*ed_at',
        '*_count',
        'count_*',
        'number_*',
        '*_number',
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
    private static $unsearchableAttributesValues = [
        'http://*',
        'https://*',
    ];

    /**
     * Checks if the given key/value is a 'searchableAttributes'.
     *
     * @param  string $key
     * @param  mixed $value
     * @param array $searchableAttributes
     *
     * @return array
     */
    public static function exist(string $key, $value, array $searchableAttributes): array
    {
        if (!is_object($value) && !is_array($value) &&
            !Str::is(self::$unsearchableAttributesKeys, $key) &&
            !Str::is(self::$unsearchableAttributesValues, $value)) {
            $searchableAttributes[] = $key;

            return $searchableAttributes;
        }

        return $searchableAttributes;
    }
}
