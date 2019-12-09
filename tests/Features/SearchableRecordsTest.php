<?php

declare(strict_types=1);

namespace Tests\Features;

use Mockery;
use function count;
use Tests\TestCase;
use Tests\Features\Fixtures\ThreadWithSearchableRecords;

final class SearchableRecordsTest extends TestCase
{
    public function testMultipleRecords(): void
    {
        $index = $this->mockIndex(ThreadWithSearchableRecords::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 3 &&
                $argument[0]['objectID'] === 'Tests\Features\Fixtures\ThreadWithSearchableRecords::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\Fixtures\ThreadWithSearchableRecords::1::1' &&
                $argument[2]['objectID'] === 'Tests\Features\Fixtures\ThreadWithSearchableRecords::1::2' &&
                $argument[0]['body'] === 'Hello!' &&
                $argument[1]['body'] === 'Hello!' &&
                $argument[2]['body'] === 'Hello!' &&
                $argument[0]['_i'] == 2 &&
                $argument[1]['_i'] == 4 &&
                $argument[2]['_i'] == 8;
        }))->andReturn($this->mockResponse());

        $index->shouldReceive('deleteBy')->with([
            'tagFilters' => [
                ['Tests\Features\Fixtures\ThreadWithSearchableRecords::1'],
            ],
        ]);

        ThreadWithSearchableRecords::create(['body' => 'Hello!']);
    }
}
