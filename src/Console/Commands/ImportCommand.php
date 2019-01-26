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
use Illuminate\Support\Collection;
use Laravel\Scout\Events\ModelsImported;
use Illuminate\Contracts\Events\Dispatcher;
use Algolia\ScoutExtended\Helpers\SearchableFinder;
use Algolia\ScoutExtended\Searchable\ObjectIdEncrypter;

final class ImportCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:import {searchable? : The name of the searchable}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Import the given searchable into the search index';

    /**
     * {@inheritdoc}
     */
    public function handle(Dispatcher $events, SearchableFinder $searchableFinder): void
    {
        foreach ($searchableFinder->fromCommand($this) as $searchable) {
            $this->call('scout:flush', ['searchable' => $searchable]);

            $events->listen(ModelsImported::class, function ($event) use ($searchable) {
                $this->resultMessage($event->models, $searchable);
            });

            $searchable::makeAllSearchable();

            $events->forget(ModelsImported::class);

            $this->output->success('All ['.$searchable.'] records have been imported.');
        }
    }

    /**
     * Prints last imported object ID to console output, if any.
     *
     * @param \Illuminate\Support\Collection $models
     * @param string $searchable
     *
     * @return void
     */
    private function resultMessage(Collection $models, string $searchable): void
    {
        if ($models->count() > 0) {
            $last = ObjectIdEncrypter::encrypt($models->last());

            $this->line('<comment>Imported ['.$searchable.'] models up to ID:</comment> '.$last);
        }
    }
}
