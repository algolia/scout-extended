<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\AlgoliaSearch\Model\Search\OperationType;
use Algolia\AlgoliaSearch\Model\Search\ScopeType;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class ReimportCommandTest extends TestCase
{
    public function testReimport(): void
    {
        factory(User::class, 5)->create();

        $indexName = (new User())->searchableAs();
        $temporaryName = 'temp_'.$indexName;

        // Set up engine + client mock with getSettings for the main index
        $this->mockIndex(User::class);

        $client = $this->mockClient();

        // getSettings for temp index (called after import to verify it exists)
        $client->shouldReceive('getSettings')->with($temporaryName)->andReturn([]);

        // operationIndex: copy settings from main to temp
        $client->shouldReceive('operationIndex')
            ->with($indexName, Mockery::on(function ($params) use ($temporaryName) {
                return is_array($params)
                    && $params['operation'] === OperationType::COPY
                    && $params['destination'] === $temporaryName
                    && $params['scope'] === [ScopeType::SETTINGS, ScopeType::SYNONYMS, ScopeType::RULES];
            }))
            ->andReturn(['taskID' => 1]);

        $client->shouldReceive('operationIndex')
            ->with($temporaryName, Mockery::on(function ($params) use ($indexName) {
                return is_array($params)
                    && $params['operation'] === OperationType::MOVE
                    && $params['destination'] === $indexName;
            }))
            ->andReturn(['taskID' => 1]);

        // saveObjects called by makeAllSearchable() via UpdateJob for the temp index
        $client->shouldReceive('saveObjects')->with($temporaryName, Mockery::on(function ($argument) {
            return count($argument) === 5 && $argument[0]['objectID'] === 'App\User::1';
        }))->andReturn(['taskID' => 1]);

        Artisan::call('scout:reimport', ['searchable' => User::class]);
    }
}
