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
use function in_array;
use \App\Models\Thread;
use Laravel\Scout\Searchable;
use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Algolia\ScoutExtended\Settings\Synchronizer;
use Algolia\ScoutExtended\Helpers\SearchableModelsFinder;
use Algolia\ScoutExtended\Contracts\SearchableCountableContract;

final class StatusCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:status {model? : The name of the searchable model}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Show the status of the index of the the given model';

    /**
     * {@inheritdoc}
     */
    public function handle(Algolia $algolia, SearchableModelsFinder $searchableModelsFinder, Synchronizer $synchronizer)
    {
        $classes = (array) $this->argument('model');

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $this->output->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');

            return 1;
        }

        $rows = [];

        $this->output->text('🔎 Analysing information from: <info>['.implode(',', $classes).']</info>');
        $this->output->newLine();
        $this->output->progressStart(count($classes));

        foreach ($classes as $searchable) {
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
                $description = "<fg=green>synchronized</>";
            }

            $row[] = $description;
            $row[] = $this->getSearchableCount($searchable);
            $row[] = $searchable::search('')->count();

            $rows[] = $row;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->output->table(['Model', 'Index', 'Settings', 'Local records', 'Remote records'], $rows);
    }

    /**
     * @param  string $searchable
     *
     * @return int
     */
    private function getSearchableCount(string $searchable): int
    {
        $instance = new $searchable;

        if ($instance instanceof SearchableCountableContract) {
            return $instance->getSearchableCount();
        }

        $softDeletes = in_array(SoftDeletes::class, class_uses_recursive($searchable), true) && config('scout.soft_delete', false);

        return $searchable::query()->when($softDeletes, function ($query) {
            $query->withTrashed();
        })->count();
    }
}
