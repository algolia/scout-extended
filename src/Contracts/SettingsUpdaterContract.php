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

interface SettingsUpdaterContract
{
    /**
     * Returns the updated version of the given settings.
     *
     * @param array $settings
     * @param string $attribute
     *
     * @return array
     */
    public function updateSettings(array $settings, string $attribute): array;
}
