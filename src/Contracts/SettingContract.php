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

namespace Algolia\ScoutExtended\Contracts;

/**
 * @internal the interface uses the public methods {@getValue}
 */
interface SettingContract
{
    /**
     * Checks if the given key/value is a setting.
     *
     * @param string $key
     * @param array|null|string $value
     * @param array $arrayValue
     *
     * @return array
     */
    public function getValue(string $key, $value, array $arrayValue): array;
}
