<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;

final class WhereQueriesTest extends TestCase
{
    public function testExplicitOperator(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                'views_count != 100',
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->where('views_count', '!=', '100')->get();
    }

    public function testInlineOperators(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                'views_count > 100',
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->where('views_count', '> 100')->get();
    }

    public function testWithDates(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                'views_count > '.($date = now())->getTimestamp(),
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->where('views_count', '>', $date)->get();
    }

    public function testOmittedOperator(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                'views_count=100',
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->where('views_count', '100')->get();
    }

    public function testWhereBetween(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                'views_count: 100 TO 200',
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->whereBetween('views_count', [100, 200])->get();
    }

    public function testWhereBetweenWithDates(): void
    {
        $date1 = now()->subMonth()->startOfMonth();
        $date2 = now()->subMonth()->endOfMonth();

        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                "created_at: {$date1->getTimestamp()} TO {$date2->getTimestamp()}",
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->whereBetween('created_at', [$date1, $date2])->get();
    }

    public function testWhereIn(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                ['id=1', 'id=2', 'id=3', 'id=4'],
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->whereIn('id', [1, 2, 3, 4])->get();
    }

    public function testMultipleWheres(): void
    {
        $this->mockIndex(User::class)->shouldReceive('search')->once()->with('foo', [
            'numericFilters' => [
                ['id=1', 'id=2'],
                'key=value',
            ],
        ])->andReturn(['hits' => []]);

        User::search('foo')->whereIn('id', [1, 2])->where('key', 'value')->get();
    }
}
