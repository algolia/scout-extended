<?php

declare(strict_types=1);

namespace Tests\Features;

use Mockery;
use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

final class ReimportCommandTest extends TestCase
{
    public function testReimport(): void
    {
        factory(User::class, 5)->create();

        $client = $this->mockClient();

        $userOriginalIndex = $this->mockIndex(User::class);

        // To check if index exists.
        $userOriginalIndex->shouldReceive('search')->once()->andReturn(['hits' => []]);

        $userTemporaryIndex = $this->mockIndex('temp_'.(new User())->searchableAs());

        $client->shouldReceive('copyIndex')->with((new User())->searchableAs(), $userTemporaryIndex->getIndexName(), [
            'scope' => [
                'settings',
                'synonyms',
                'rules',
            ],
        ])->andReturn($this->mockResponse());

        $userTemporaryIndex->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 5 && $argument[0]['objectID'] === 'App\User::1';
        }))->andReturn($this->mockResponse());

        $userTemporaryIndex->shouldReceive('search')->andReturn(['nbHits' => 5]);

        $client->shouldReceive('moveIndex')->with($userTemporaryIndex->getIndexName(), 'users')
            ->andReturn($this->mockResponse());

        Artisan::call('scout:reimport', ['searchable' => User::class]);
    }
}
