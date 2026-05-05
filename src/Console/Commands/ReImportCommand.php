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

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Model\Search\OperationType;
use Algolia\AlgoliaSearch\Model\Search\ScopeType;
use Algolia\ScoutExtended\Helpers\SearchableFinder;
use function count;
use Illuminate\Console\Command;

class ReImportCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:reimport {searchable? : The name of the searchable}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reimport the given searchable into the search index';

    /**
     * @var string
     */
    private static $prefix = 'temp';

    /**
     * {@inheritdoc}
     */
    public function handle(
        SearchClient $client,
        SearchableFinder $searchableModelsFinder
    ): void {
        $searchables = $searchableModelsFinder->fromCommand($this);

        $config = config();

        $scoutPrefix = $config->get('scout.prefix');

        $this->output->text('🔎 Importing: <info>['.implode(',', $searchables).']</info>');
        $this->output->newLine();
        $this->output->progressStart(count($searchables) * 3);

        foreach ($searchables as $searchable) {
            $indexName = (new $searchable)->searchableAs();
            $temporaryName = $this->getTemporaryIndexName($indexName);

            tap($this->output)->progressAdvance()->text("Creating temporary index <info>{$temporaryName}</info>");

            try {
                $client->getSettings($indexName);

                $response = $client->operationIndex($indexName, [
                    'operation' => OperationType::COPY,
                    'destination' => $temporaryName,
                    'scope' => [ScopeType::SETTINGS, ScopeType::SYNONYMS, ScopeType::RULES],
                ]);
                $client->waitForTask($temporaryName, $response['taskID']);
            } catch (NotFoundException $e) {
                // ..
            }

            tap($this->output)->progressAdvance()->text("Importing records to index <info>{$temporaryName}</info>");

            // Force disable queueing to prevent race conditions in indexing with large number of records.
            $useQueues = $config->get('scout.queue');
            $config->set('scout.queue', false);

            try {
                $config->set('scout.prefix', self::$prefix.'_'.$scoutPrefix);
                $searchable::makeAllSearchable();
            } finally {
                $config->set('scout.prefix', $scoutPrefix);
            }

            $config->set('scout.queue', $useQueues);

            tap($this->output)->progressAdvance()
                ->text("Replacing index <info>{$indexName}</info> by index <info>{$temporaryName}</info>");

            try {
                $client->getSettings($temporaryName);

                $response = $client->operationIndex($temporaryName, [
                    'operation' => OperationType::MOVE,
                    'destination' => $indexName,
                ]);

                if ($config->get('scout.synchronous', false)) {
                    $client->waitForTask($indexName, $response['taskID']);
                }
            } catch (NotFoundException $e) {
                if (!$client->indexExists($indexName)) {
                    $response = $client->setSettings($indexName, ['attributesForFaceting' => null]);
                    $client->waitForTask($indexName, $response['taskID']);
                }
            }
        }

        tap($this->output)->success('All ['.implode(',', $searchables).'] records have been imported')->newLine();
    }

    /**
     * Get a temporary index name.
     *
     * @param string $indexName
     *
     * @return string
     */
    private function getTemporaryIndexName(string $indexName): string
    {
        return self::$prefix.'_'.$indexName;
    }
}
