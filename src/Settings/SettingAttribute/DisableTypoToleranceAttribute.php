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

class DisableTypoToleranceAttribute implements SettingContract
{
    /**
     * @var string[]
     */
    private static $disableTypoToleranceOnAttributesKeys = [
        'slug',
        '*_slug',
        'slug_*',
        '*code*',
        '*sku*',
        '*reference*',
    ];

    /**
     * Checks if the given key/value is a 'disableTypoToleranceOnAttributes'.
     *
     * @param  string $key
     * @param  array|null|string $value
     * @param  array $disableTypoToleranceOnAttributes
     *
     * @return array
     */
    public function getValue(string $key, $value, array $disableTypoToleranceOnAttributes): array
    {
        if (is_string($key) && Str::is(self::$disableTypoToleranceOnAttributesKeys, $key)) {
            $disableTypoToleranceOnAttributes[] = $key;
        }

        return $disableTypoToleranceOnAttributes;
    }
}
