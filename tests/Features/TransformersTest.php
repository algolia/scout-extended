<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\ScoutExtended\Jobs\UpdateJob;
use Algolia\ScoutExtended\Transformers\ConvertDatesToTimestamps;
use Algolia\ScoutExtended\Transformers\ConvertNumericStringsToNumbers;
use App\Thread;
use App\User;
use Tests\Features\Fixtures\ThreadWithSearchableArrayOnTrait;
use function is_int;
use Tests\Features\Fixtures\ThreadWithSearchableArray;
use Tests\Features\Fixtures\ThreadWithSearchableArrayUsingTransform;
use Tests\TestCase;

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

    public function testToSearchableArrayCanBeUsedFromTraits(): void
    {
        $threadsIndexMock = $this->mockIndex('threads');

        $threadsIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            // Assert 'something' doesn't exist.
            return empty($argument[0]['something']);
        }));

        $thread = factory(Thread::class)->create();

        $threadWithSearchableArrayOnTrait = new ThreadWithSearchableArrayOnTrait($thread->toArray());

        $threadsIndexMock = $this->mockIndex($threadWithSearchableArrayOnTrait->searchableAs());

        $threadsIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            // Assert 'something' is defined.
            return $argument[0]['something'] === 99;
        }));

        // Update.
        $threadWithSearchableArrayOnTrait->created_at = now();

        dispatch(new UpdateJob(collect([$threadWithSearchableArrayOnTrait])));
    }

    public function testConvertDatesToTimestamps(): void
    {
        $thread = factory(Thread::class)->create();

        $array = (new ConvertDatesToTimestamps())->transform($thread, $thread->toSearchableArray());

        $this->assertSame($thread->created_at->getTimestamp(), $array['created_at']);
    }

    public function testConvertNumericStringsToNumbers(): void
    {
        $user = factory(User::class)->create();

        $array = (new ConvertNumericStringsToNumbers())->transform($user, $user->toSearchableArray());

        $this->assertSame(100, $array['views_count']);
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
