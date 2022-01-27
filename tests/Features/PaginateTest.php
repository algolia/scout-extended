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

        $prevValue = config('scout.synchronous');
        config(['scout.synchronous' => true]);
        factory(User::class, 10)->create();
        config(['scout.synchronous' => $prevValue]);
    }

    public function testPaginationWithCallback(): void
    {
        $results = User::search('')->query(function ($query) {
            return $query->orderBy('id', 'desc');
        })->paginate(5);

        $this->assertEquals(10, $results->total());
        $this->assertEquals(2, $results->lastPage());
    }

    public function testPaginationWithoutCallback(): void
    {
        $results = User::search('')->orderBy('id', 'desc')->paginate(5);

        $this->assertEquals(10, $results->total());
        $this->assertEquals(2, $results->lastPage());
    }
}
