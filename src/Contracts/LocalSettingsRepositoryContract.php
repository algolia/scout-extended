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

use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\ScoutExtended\Settings\Settings;

interface LocalSettingsRepositoryContract
{
    /**
     * Checks if the given index settings exists.
     *
     * @param  \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return bool
     */
    public function exists(SearchIndex $index): bool;

    /**
     * Get the settings path of the given index name.
     *
     * @param  \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return string
     */
    public function getPath(SearchIndex $index): string;

    /**
     * Find the settings of the given Index.
     *
     * @param \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return \Algolia\ScoutExtended\Settings\Settings
     */
    public function find(SearchIndex $index): Settings;
}
