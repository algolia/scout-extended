<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;

final class WhereQueriesTest extends TestCase
{
    public function testOperators(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->with('foo', [
            'numericFilters' => [
                'views_count > 100',
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->where('views_count', '> 100')->get();
    }

    public function testEqualOperator(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->with('foo', [
            'numericFilters' => [
                'views_count=100',
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->where('views_count', '100')->get();
    }
}
