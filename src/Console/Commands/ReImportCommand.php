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
use Algolia\AlgoliaSearch\Index;
use Algolia\ScoutExtended\Algolia;
use Algolia\ScoutExtended\Helpers\SearchableFinder;
use Algolia\ScoutExtended\Searchable\RecordsCounter;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;

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
    public function handle(
        Algolia $algolia,
        SearchableFinder $searchableModelsFinder,
        RecordsCounter $recordsCounter
    ) {
        $searchables = $searchableModelsFinder->fromCommand($this);

        $config = config();

        $scoutPrefix = $config->get('scout.prefix');

        $this->output->text('🔎 Importing: <info>['.implode(',', $searchables).']</info>');
        $this->output->newLine();
        $this->output->progressStart(count($searchables) * 3);

        foreach ($searchables as $searchable) {
            $index = $algolia->index($searchable);
            $temporaryName = $this->getTemporaryIndexName($index);

            tap($this->output)->progressAdvance()->text("Creating temporary index <info>{$temporaryName}</info>");

            $algolia->client()->copyIndex($index->getIndexName(), $temporaryName, [
                'scope' => [
                    'settings',
                    'synonyms',
                    'rules',
                ],
            ])->wait();

            tap($this->output)->progressAdvance()->text("Importing records to index <info>{$temporaryName}</info>");

            try {
                $config->set('scout.prefix', self::$prefix.'_'.$scoutPrefix);
                $searchable::makeAllSearchable();
                while ($this->waitingForRecordsImported($recordsCounter, $searchable)) {
                    sleep(1);
                }
            } finally {
                $config->set('scout.prefix', $scoutPrefix);
            }

            tap($this->output)->progressAdvance()->text("Replacing index <info>{$index->getIndexName()}</info> by index <info>{$temporaryName}</info>");

            $algolia->client()->moveIndex($temporaryName, $index->getIndexName())->wait();
        }

        tap($this->output)->success('All ['.implode(',', $searchables).'] records have been imported')->newLine();
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
    private function waitingForRecordsImported(RecordsCounter $recordsCounter, string $searchable): bool
    {
        $result = false;

        try {
            $result = $recordsCounter->local($searchable) !== $recordsCounter->remote($searchable);
        } catch (NotFoundException $e) {
            // ..
        }

        return $result;
    }
}
