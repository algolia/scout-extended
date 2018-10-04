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

interface SearchableCountableContract
{
    /**
     * Get the number of searchable records.
     *
     * @return int
     */
    public function getSearchableCount(): int;
}
