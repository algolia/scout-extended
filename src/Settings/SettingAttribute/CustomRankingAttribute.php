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

class CustomRankingAttribute implements SettingContract
{
    /**
     * @var string[]
     */
    private static $customRankingKeys = [
        '*ed_at',
        'count_*',
        '*_count',
        'number_*',
        '*_number',
    ];

    /**
     * Checks if the given key/value is a 'CustomRanking'.
     *
     * @param  string $key
     * @param  mixed $value
     * @param array $customRanking
     *
     * @return array
     */
    public static function exist(string $key, $value, array $customRanking): array
    {
        if (Str::is(self::$customRankingKeys, $key)) {
            $customRanking[] = "desc({$key})";

            return $customRanking;
        }

        return $customRanking;
    }
}
