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

namespace Algolia\ScoutExtended\Console\Commands;

use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;
use Algolia\ScoutExtended\Helpers\SearchableFinder;

final class FlushCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:flush {searchable? : The name of the searchable}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Flush the index of the the given searchable';

    /**
     * {@inheritdoc}
     */
    public function handle(Algolia $algolia, SearchableFinder $searchableFinder): void
    {
        foreach ($searchableFinder->fromCommand($this) as $searchable) {
            $searchable::removeAllFromSearch();

            $this->output->success('All ['.$searchable.'] records have been flushed.');
        }
    }
}
