<?php

declare(strict_types=1);

namespace Tests\Features;

use Mockery;
use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

final class ReimportCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(database_path('migrations'));
        $this->artisan('migrate:fresh', ['--database' => 'testbench'])->run();
    }

    public function testReimport(): void
    {
        factory(User::class, 5)->create();

        $client = $this->mockClient();

        $userOriginalIndex = $this->mockIndex(User::class);
        $userTemporaryIndex = $this->mockIndex('temp_'.(new User())->searchableAs());

        $client->shouldReceive('copyIndex')->with($userOriginalIndex->getIndexName(), $userTemporaryIndex->getIndexName(), [
            'scope' => [
                'settings',
                'synonyms',
                'rules',
            ],
        ])->andReturn($this->mockResponse());

        $userTemporaryIndex->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 5 && $argument[0]['objectID'] === 1;
        }));

        $userTemporaryIndex->shouldReceive('search')->andReturn(['nbHits' => 5]);

        $client->shouldReceive('moveIndex')->andReturn($this->mockResponse());

        Artisan::call('scout:reimport', ['model' => User::class]);
    }
}
