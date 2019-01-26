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

use function count;
use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;
use Algolia\ScoutExtended\Settings\Synchronizer;
use Algolia\ScoutExtended\Helpers\SearchableFinder;
use Algolia\ScoutExtended\Searchable\RecordsCounter;

final class StatusCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:status {searchable? : The name of the searchable}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Show the status of the index of the the given searchable';

    /**
     * {@inheritdoc}
     */
    public function handle(
        Algolia $algolia,
        SearchableFinder $searchableFinder,
        Synchronizer $synchronizer,
        RecordsCounter $recordsCounter
    ): void {
        $searchables = $searchableFinder->fromCommand($this);

        $rows = [];

        $this->output->text('🔎 Analysing information from: <info>['.implode(',', $searchables).']</info>');
        $this->output->newLine();
        $this->output->progressStart(count($searchables));

        foreach ($searchables as $searchable) {
            $row = [];
            $instance = $this->laravel->make($searchable);
            $index = $algolia->index($instance);
            $row[] = $searchable;
            $row[] = $instance->searchableAs();

            $status = $synchronizer->analyse($index);
            $description = $status->toHumanString();
            if (! $status->bothAreEqual()) {
                $description = "<fg=red>$description</>";
            } else {
                $description = '<fg=green>Synchronized</>';
            }

            $row[] = $description;
            $row[] = $recordsCounter->local($searchable);
            $row[] = $recordsCounter->remote($searchable);

            $rows[] = $row;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->output->table(['Searchable', 'Index', 'Settings', 'Local records', 'Remote records'], $rows);
    }
}
