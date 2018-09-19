<?php

declare(strict_types=1);

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use Tests\Models\User;
use Mockery\MockInterface;
use Algolia\AlgoliaSearch\Index;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\AlgoliaEngine;
use Algolia\LaravelScoutExtended\Facades\Algolia;

final class BuilderTest extends TestCase
{
    public function testCount(): void
    {
        $this->mockEngine()->expects('search')->andReturn([
            'nbHits' => 5,
        ]);

        $this->assertEquals(5, User::search('')->count());
    }

    public function testWith(): void
    {
        $this->mockIndex()->expects('search')->with('foo', Mockery::subset(['aroundRadius' => 1]))->andReturn(['hits' => []]);

        User::search('foo')->with(['aroundRadius' => 1])->get();
    }

    public function testAroundLatLng(): void
    {
        $this->mockIndex()->expects('search')->with('bar', Mockery::subset(['aroundLatLng' => '48.8566,2.3522']))->andReturn(['hits' => []]);

        User::search('bar')->aroundLatLng(48.8566, 2.3522)->get();
    }

    public function testHydrate(): void
    {
        $this->mockEngine()->shouldReceive('search')->andReturn([
            'hits' => [
                [
                    'name' => 'Foo',
                    'email' => 'bar@example.com',
                ],
            ],
        ]);

        $users = User::search('foo@bar.com')->hydrate();

        $this->assertCount(1, $users);
        $this->assertInstanceOf(User::class, $users->first());
        $this->assertEquals('Foo', $users->first()->name);
        $this->assertEquals('bar@example.com', $users->first()->email);
    }

    private function mockEngine(): MockInterface
    {
        $engineMock = Mockery::mock(AlgoliaEngine::class)->makePartial()->shouldIgnoreMissing();

        $managerMock = Mockery::mock(EngineManager::class)->makePartial()->shouldIgnoreMissing();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(EngineManager::class, $managerMock);

        return $engineMock;
    }

    private function mockIndex(): MockInterface
    {
        $indexMock = Mockery::mock(Index::class);

        $clientMock = Mockery::mock(Algolia::client())->makePartial();

        $clientMock->expects('initIndex')->andReturn($indexMock);

        $engineMock = Mockery::mock(AlgoliaEngine::class, [$clientMock])->makePartial();

        $managerMock = Mockery::mock(EngineManager::class)->makePartial();

        $managerMock->shouldReceive('driver')->andReturn($engineMock);

        $this->swap(EngineManager::class, $managerMock);

        return $indexMock;
    }
}
