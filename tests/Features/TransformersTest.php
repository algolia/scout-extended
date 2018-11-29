<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use App\Thread;
use Tests\TestCase;
use function is_int;
use Algolia\ScoutExtended\Jobs\UpdateJob;
use Algolia\ScoutExtended\Contracts\TransformerContract;
use Algolia\ScoutExtended\Transformers\ConvertDatesToTimestamps;
use Algolia\ScoutExtended\Transformers\ConvertNumericStringsToNumbers;

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

        $array = (new ConvertDatesToTimestamps())->transform($thread, $thread->toSearchableArray());

        $this->assertEquals($thread->created_at->getTimestamp(), $array['created_at']);
    }

    public function testConvertNumericStringsToNumbers(): void
    {
        $user = factory(User::class)->create();

        $array = (new ConvertNumericStringsToNumbers())->transform($user, $user->toSearchableArray());

        $this->assertEquals(100, $array['views_count']);
    }

    public function testTransformMethod(): void
    {
        $thread = factory(Thread::class)->create();

        $threadWithSearchableArrayUsingTransform = new ThreadWithSearchableArrayUsingTransform($thread->toArray());

        $threadsIndexMock = $this->mockIndex($threadWithSearchableArrayUsingTransform->searchableAs());

        $threadsIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            // Assert dates are NOT converted to integers:
            return $argument[0]['created_at'] === 'Foo';
        }));

        $threadWithSearchableArrayUsingTransform->created_at = now();

        dispatch(new UpdateJob(collect([$threadWithSearchableArrayUsingTransform])));
    }
}

class ThreadWithSearchableArray extends Thread
{
    public function toSearchableArray(): array
    {
        return $this->toArray();
    }
}

class ThreadWithSearchableArrayUsingTransform extends Thread
{
    public function toSearchableArray(): array
    {
        return $this->transform($this->toArray(), [
            ConvertToFoo::class,
        ]);
    }
}

class ConvertToFoo implements TransformerContract
{
    public function transform($searchable, array $array): array
    {
        foreach ($array as $key => $value) {
            $array[$key] = 'Foo';
        }

        return $array;
    }
}
