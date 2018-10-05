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
use Algolia\AlgoliaSearch\Index;
use Algolia\ScoutExtended\Algolia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\ScoutExtended\Helpers\SearchableModelsFinder;
use Algolia\ScoutExtended\Contracts\SearchableCountableContract;

final class ReImportCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:reimport {model? : The name of the searchable model}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reimport the given model into the search index';

    /**
     * @var string
     */
    private static $prefix = 'temp';

    /**
     * {@inheritdoc}
     */
    public function handle(Algolia $algolia, SearchableModelsFinder $searchableModelsFinder)
    {
        $searchables = (array) $this->argument('model');

        if (empty($searchables) && empty($searchables = $searchableModelsFinder->find())) {
            $this->output->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');

            return 1;
        }

        $config = config();

        $scoutPrefix = $config->get('scout.prefix');

        $this->output->text('🔎 Importing: <info>['.implode(',', $searchables).']</info>');
        $this->output->newLine();
        $this->output->progressStart(count($searchables) * 3);

        foreach ($searchables as $searchable) {
            $index = $algolia->index($searchable);
            $temporaryName = $this->getTemporaryIndexName($index);
            $this->output->progressAdvance();
            $this->output->text("Creating temporary index <info>{$temporaryName}</info>");
            $algolia->client()->copyIndex($index->getIndexName(), $temporaryName, [
                'scope' => [
                    'settings',
                    'synonyms',
                    'rules',
                ],
            ])->wait();
            $this->output->progressAdvance();

            $this->output->text("Importing records to index <info>{$temporaryName}</info>");
            try {
                $config->set('scout.prefix', self::$prefix.'_'.$scoutPrefix);
                $searchable::makeAllSearchable();
                do {
                    sleep(1);
                } while ($this->waitingForRecordsImported($searchable));
            } finally {
                $config->set('scout.prefix', $scoutPrefix);
            }
            $this->output->progressAdvance();

            $this->output->text("Replacing index <info>{$index->getIndexName()}</info> by index <info>{$temporaryName}</info>");
            $algolia->client()->moveIndex($temporaryName, $index->getIndexName())->wait();
            $algolia->client()->deleteIndex($temporaryName)->wait();
        }

        $this->output->success('All ['.implode(',', $searchables).'] records have been imported');
        $this->output->newLine();
    }

    /**
     * Get a temporary index name.
     *
     * @param \Algolia\AlgoliaSearch\Index $index
     *
     * @return string
     */
    private function getTemporaryIndexName(Index $index): string
    {
        return self::$prefix.'_'.$index->getIndexName();
    }

    /**
     * @param  string $searchable
     *
     * @return bool
     */
    private function waitingForRecordsImported(string $searchable): bool
    {
        try {
            return $searchable::search('')->count() !== $this->getSearchableCount($searchable);
        } catch (NotFoundException $e) {
            // ..
        }

        return false;
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
