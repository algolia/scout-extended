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

use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
    public function handle(Dispatcher $events, SearchableFinder $searchableFinder, SearchClient $client): void
    {
        foreach ($searchableFinder->fromCommand($this) as $searchable) {

            $events->listen(ModelsImported::class, function ($event) use ($searchable) {
                $this->resultMessage($event->models, $searchable);
            });

            $searchable::makeAllSearchable();

            $this->line("Checking for any records that need to be deleted");

            $model = new $searchable();
            $index = $client->initIndex($model->searchableAs());


            $builder = new \Laravel\Scout\Builder($model, '');

            // check for objects that should be deleted.
            $iterator = $index->browseObjects([
                'hitsPerPage' => 100
            ]);

            foreach ($iterator as $hit) {
                $searchKey = [ObjectIdEncrypter::decryptSearchableKey($hit['objectID'])];

                try {
                    $searchableObject = $model->getScoutModelsByIds($builder, $searchKey);

                    if ($searchableObject->count() === 1) {
                        if (! $searchableObject[0]->shouldBeSearchable()) {
                            Log::error("ImportCommand delete item unsearchable", [
                                'object' => $searchable,
                                'id' => $hit['objectID']
                            ]);
                            $searchableObject[0]->unsearchable();
                        }
                    } else {
                        Log::error("ImportCommand delete item", [
                            'object' => $searchable,
                            'id' => $hit['objectID']
                        ]);
                        $index->deleteObject($hit['objectID']);
                        $this->line("Found item to delete " . $hit['objectID']);
                    }
                } catch (QueryException $e) {
                    // this should only happen when they are type issues like id is int, and the key is alpha.
                    $index->deleteObject($hit['objectID']);
                    $this->line("Found item to delete " . $hit['objectID']);
                }
            }

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
