<?php

declare(strict_types=1);

namespace Tests\Features;

use Mockery;
use function count;
use Tests\TestCase;
use Tests\Features\Fixtures\ThreadMultipleSplits;
use Tests\Features\Fixtures\ThreadWithSplitterClass;
use Tests\Features\Fixtures\ThreadWithValueReturned;
use Tests\Features\Fixtures\ThreadWithSplitterInstance;

final class SplittersTest extends TestCase
{
    public function testRecordsAreSplittedByASplitter(): void
    {
        $index = $this->mockIndex(ThreadWithSplitterClass::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 4 && $argument[0]['h1'] === 'Hello Foo!' && $argument[0]['importance'] === 0 && $argument[1]['h1'] === 'Hello Foo!' && $argument[1]['h2'] === 'Hello Bar!' && $argument[1]['importance'] === 1 && $argument[2]['h1'] === 'Hello Baz!' && $argument[2]['importance'] === 0 && $argument[3]['h1'] === 'Hello Baz!' && $argument[3]['p'] === 'Hello Bam!' && $argument[3]['importance'] === 6;
        }))->andReturn($this->mockResponse());

        $index->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['Tests\Features\Fixtures\ThreadWithSplitterClass::1'],
            ],
        ]);

        $body = implode('', [
            '<h1>Hello Foo!</h1>',
            '<h2>Hello Bar!</h2>',
            '<h1>Hello Baz!</h1>',
            '<p>Hello Bam!</p>',
        ]);

        ThreadWithSplitterClass::create(['body' => $body]);
    }

    public function testRecordsAreTextSplittedByValue(): void
    {
        $index = $this->mockIndex(ThreadWithValueReturned::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 2 && $argument[0]['objectID'] === 'Tests\Features\Fixtures\ThreadWithValueReturned::1::0' && $argument[1]['objectID'] === 'Tests\Features\Fixtures\ThreadWithValueReturned::1::1' && $argument[0]['body'] === 'Hello Foo!' && $argument[1]['body'] === 'Hello Bar!';
        }))->andReturn($this->mockResponse());

        $index->shouldReceive('deleteBy')->with([
            'tagFilters' => [
                ['Tests\Features\Fixtures\ThreadWithValueReturned::1'],
            ],
        ]);

        $body = implode(',', [
            'Hello Foo!',
            'Hello Bar!',
        ]);

        ThreadWithValueReturned::create(['body' => $body]);
    }

    public function testRecordsAreTextSplittedSplitterInstance(): void
    {
        $index = $this->mockIndex(ThreadWithSplitterInstance::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 2 && $argument[0]['h1'] === 'Hello Foo!' && $argument[0]['importance'] === 0 && $argument[1]['h1'] === 'Hello Bar!' && $argument[1]['importance'] === 0;
        }))->andReturn($this->mockResponse());

        $index->shouldReceive('deleteBy')->with([
            'tagFilters' => [
                ['Tests\Features\Fixtures\ThreadWithSplitterInstance::1'],
            ],
        ]);

        $body = implode('', [
            '<h1>Hello <strong>Foo!</strong></h1>',
            '<h1>Hello <strong>Bar</strong>!</h1>',
        ]);

        ThreadWithSplitterInstance::create(['body' => $body]);
    }

    public function testRecordsCanHaveMultipleSplits(): void
    {
        $index = $this->mockIndex(ThreadMultipleSplits::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 2 && $argument[0]['h1'] === 'Hello Foo!' && $argument[0]['importance'] === 0 && $argument[1]['h1'] === 'Hello Bar!' && $argument[1]['importance'] === 0;
        }))->andReturn($this->mockResponse());

        $index->shouldReceive('deleteBy')->with([
            'tagFilters' => [
                ['Tests\Features\Fixtures\ThreadMultipleSplits::1'],
            ],
        ]);

        $body = implode('', [
            '<h1>Hello <strong>Foo!</strong></h1>',
            '<h1>Hello <strong>Bar</strong>!</h1>',
        ]);

        ThreadMultipleSplits::create([
            'slug' => 'first-second',
            'description_at_the_letter' => 2,
            'body' => $body,
        ]);
    }

    public function testSearchMethod(): void
    {
        $index = $this->mockIndex(ThreadWithValueReturned::class);

        $index->shouldReceive('saveObjects')->twice();
        $index->shouldReceive('deleteBy')->twice();

        $body = implode('', [
            '<h1>Hello <strong>Foo!</strong></h1>',
            '<h1>Hello <strong>Bar</strong>!</h1>',
        ]);

        ThreadWithValueReturned::create(['body' => $body]);
        ThreadWithValueReturned::create(['body' => 'Hello John']);

        $index->shouldReceive('search')->once()->andReturn([
            'hits' => [
                [
                    'body' => 'Hello Foo!',
                    'id' => 1,
                    'objectID' => "Tests\Features\Fixtures\ThreadWithValueReturned::1::0",
                ],
                [
                    'body' => 'Hello Bar!',
                    'id' => 1,
                    'objectID' => "Tests\Features\Fixtures\ThreadWithValueReturned::1::1",
                ],
                [
                    'body' => 'Hello John!',
                    'id' => 2,
                    'objectID' => "Tests\Features\Fixtures\ThreadWithValueReturned::2::0",
                ],
            ],
        ]);
        $models = ThreadWithValueReturned::search('Hello')->get();
        $this->assertSame(3, $models->count());
        $this->assertInstanceOf(ThreadWithValueReturned::class, $models[0]);
        $this->assertInstanceOf(ThreadWithValueReturned::class, $models[1]);
    }
}
