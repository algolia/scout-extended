<?php

declare(strict_types=1);

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use Tests\Models\User;

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
        $this->mockIndex(User::class)->expects('search')->with('foo', Mockery::subset(['aroundRadius' => 1]))->andReturn(['hits' => []]);

        User::search('foo')->with(['aroundRadius' => 1])->get();
    }

    public function testAroundLatLng(): void
    {
        $this->mockIndex(User::class)->expects('search')->with('bar', Mockery::subset(['aroundLatLng' => '48.8566,2.3522']))->andReturn(['hits' => []]);

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
}
