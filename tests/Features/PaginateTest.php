<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;

final class PaginateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $users = factory(User::class, 10)->make()->toArray();
        factory(User::class)->createMany($users);
    }

    public function testPaginationWithCallback(): void
    {
        $userIndexMock = $this->mockIndex(User::class, $this->defaults());
        $userIndexMock->expects('search')->once();

        $results = User::search('')->query(function ($query) {
            return $query->orderBy('id', 'desc');
        })->paginate(5);

        $this->assertEquals(10, $results->total());
        $this->assertEquals(2, $results->lastPage());
    }

    public function testPaginationWithoutCallback(): void
    {
        $userIndexMock = $this->mockIndex(User::class, $this->defaults());
        $userIndexMock->expects('search')->once();

        $results = User::search('')->orderBy('id', 'desc')->paginate(5);

        $this->assertEquals(10, $results->total());
        $this->assertEquals(2, $results->lastPage());
    }
}
