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

use Laravel\Scout\Searchable;
use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;
use Algolia\ScoutExtended\Helpers\SearchableModelsFinder;

final class ImportCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:import {model? : The name of the searchable model}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Import the given model into the search index';

    /**
     * {@inheritdoc}
     */
    public function handle(SearchableModelsFinder $searchableModelsFinder)
    {
        $classes = (array) $this->argument('model');

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $this->output->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');

            return 1;
        }

        foreach ($classes as $class) {
            $this->call('scout:flush', ['model' => $class]);

            $class::makeAllSearchable();

            $this->output->success('All ['.$class.'] records have been imported.');
        }
    }
}
