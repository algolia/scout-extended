<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\ScoutExtended\Jobs\UpdateJob;
use Algolia\ScoutExtended\Transformers\ConvertDatesToTimestamps;
use Algolia\ScoutExtended\Transformers\ConvertNumericStringsToNumbers;
use App\Thread;
use App\User;
use Tests\TestCase;
use function is_int;

final class TransformersTest extends TestCase
{
    public function testAppliedByDefault(): void
    {
        $threadsIndexMock = $this->mockIndex('threads');

        $threadsIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            // Assert dates are converted to integers:
            return is_int($argument[0]['created_at']);
        }));

        $thread = factory(Thread::class)->create();

        $threadWithSearchableArray = new ThreadWithSearchableArray($thread->toArray());

        $threadsIndexMock = $this->mockIndex($threadWithSearchableArray->searchableAs());

        $threadsIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            // Assert dates are NOT converted to integers:
            return ! is_int($argument[0]['created_at']);
        }));

        $threadWithSearchableArray->created_at = now();

        dispatch(new UpdateJob(collect([$threadWithSearchableArray])));
    }

    public function testConvertDatesToTimestamps(): void
    {
        $thread = factory(Thread::class)->create();

        $array = (new ConvertDatesToTimestamps())($thread, $thread->toSearchableArray());

        $this->assertEquals($thread->created_at->getTimestamp(), $array['created_at']);
    }

    public function testConvertNumericStringsToNumbers(): void
    {
        $user = factory(User::class)->create();

        $array = (new ConvertNumericStringsToNumbers())($user, $user->toSearchableArray());

        $this->assertEquals(100, $array['views_count']);
    }
}

class ThreadWithSearchableArray extends Thread
{
    public function toSearchableArray(): array
    {
        return $this->toArray();
    }
}
