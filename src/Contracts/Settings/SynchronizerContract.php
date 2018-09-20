<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\LaravelScoutExtended\Contracts\Settings;

use Algolia\AlgoliaSearch\Interfaces\IndexInterface;

interface SynchronizerContract
{
    /**
     * Back up the settings of the given index.
     *
     * @param \Algolia\AlgoliaSearch\Interfaces\IndexInterface $index
     *
     * @return void
     */
    public function backup(IndexInterface $index): void;
}
