<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

final class ReimportCommandTest extends TestCase
{
    public function testReimport(): void
    {
        factory(User::class, 5)->create();

        $client = $this->mockClient();

        $this->mockIndex(User::class);

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
