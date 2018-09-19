<?php

declare(strict_types=1);

namespace Tests\Integration;

use UserSeeder;
use Tests\Models\User;

final class BuilderTest extends TestCase
{
    public function testCount(): void
    {
        $this->assertEquals(UserSeeder::COUNT, User::search('')->count());
    }

    public function testWithAndAroundLatLng(): void
    {
        factory(User::class)->make(['latitude' => 48.8566, 'longitude' => 2.3522])->save();

        $this->assertEquals(1, User::search('')->with(['aroundRadius' => 1])->aroundLatLng(48.8566, 2.3522)->count());
    }

    public function testHydrate(): void
    {
        $userSaved = tap(factory(User::class)->make(['email' => 'foo@bar.com']))->save();

        $userSearched = User::search('foo@bar.com')->hydrate()->first();

        $this->assertEquals($userSaved->id, $userSearched->id);
    }
}
