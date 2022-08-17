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

use Algolia\ScoutExtended\Settings\Settings;

interface LocalSettingsRepositoryContract
{
    /**
     * Checks if the given index settings exists.
     *
     * @param string $index
     *
     * @return bool
     */
    public function exists($index): bool;

    /**
     * Get the settings path of the given index name.
     *
     * @param string $index
     *
     * @return string
     */
    public function getPath($index): string;

    /**
     * Find the settings of the given Index.
     *
     * @param string $index
     *
     * @return \Algolia\ScoutExtended\Settings\Settings
     */
    public function find($index): Settings;
}
