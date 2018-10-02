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
use Algolia\ScoutExtended\Helpers\SearchableModelsFinder;

final class FlushCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:flush {model? : The name of the searchable model}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Flush the index of the the given model';

    /**
     * {@inheritdoc}
     */
    public function handle(Algolia $algolia, SearchableModelsFinder $searchableModelsFinder)
    {
        $classes = (array) $this->argument('model');

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $this->output->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');

            return 1;
        }

        foreach ($classes as $class) {
            $algolia->index($class)->clear();

            $this->output->success('All ['.$class.'] records have been flushed.');
        }
    }
}
