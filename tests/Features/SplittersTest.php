<?php

declare(strict_types=1);

namespace Tests\Features;

use function count;
use Mockery;
use Tests\Features\Fixtures\ThreadMultipleSplits;
use Tests\Features\Fixtures\ThreadWithSplitterClass;
use Tests\Features\Fixtures\ThreadWithSplitterInstance;
use Tests\Features\Fixtures\ThreadWithValueReturned;
use Tests\TestCase;

final class SplittersTest extends TestCase
{
    public function testRecordsAreSplittedByASplitter(): void
    {
        $index = $this->mockIndex(ThreadWithSplitterClass::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 2 &&
                $argument[0]['objectID'] === 'Tests\Features\Fixtures\ThreadWithSplitterClass::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\Fixtures\ThreadWithSplitterClass::1::1' &&
                $argument[0]['body'] === 'Hello Foo!' && $argument[1]['body'] === 'Hello Bar!';
        }))->andReturn($this->mockResponse());

        $index->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['Tests\Features\Fixtures\ThreadWithSplitterClass::1'],
            ],
        ]);

        $body = implode('', [
            '<p>Hello <a href="example.com">Foo</a>!</p>',
            '<p>Hello <a href="example.com">Bar</a>!</p>',
        ]);

        ThreadWithSplitterClass::create(['body' => $body]);
    }

    public function testRecordsAreTextSplittedByValue(): void
    {
        $index = $this->mockIndex(ThreadWithValueReturned::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 2 &&
                $argument[0]['objectID'] === 'Tests\Features\Fixtures\ThreadWithValueReturned::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\Fixtures\ThreadWithValueReturned::1::1' &&
                $argument[0]['body'] === 'Hello Foo!' && $argument[1]['body'] === 'Hello Bar!';
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
            return count($argument) === 2 &&
                $argument[0]['objectID'] === 'Tests\Features\Fixtures\ThreadWithSplitterInstance::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\Fixtures\ThreadWithSplitterInstance::1::1' &&
                $argument[0]['body'] === 'Hello Foo!' && $argument[1]['body'] === 'Hello Bar!';
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
            return count($argument) === 8 && $argument[0]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::1' &&
                $argument[2]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::2' &&
                $argument[3]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::3' &&
                $argument[4]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::4' &&
                $argument[5]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::5' &&
                $argument[6]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::6' &&
                $argument[7]['objectID'] === 'Tests\Features\Fixtures\ThreadMultipleSplits::1::7' &&
                $argument[0]['body'] === 'Hello Foo!' && $argument[0]['slug'] === 'first' &&
                $argument[0]['description_at_the_letter'] === 1 && $argument[1]['body'] === 'Hello Bar!' &&
                $argument[1]['slug'] === 'first' && $argument[1]['description_at_the_letter'] === 1 &&
                $argument[2]['body'] === 'Hello Foo!' && $argument[2]['slug'] === 'first' &&
                $argument[2]['description_at_the_letter'] === 2 && $argument[3]['body'] === 'Hello Bar!' &&
                $argument[3]['slug'] === 'first' && $argument[3]['description_at_the_letter'] === 2 &&
                $argument[4]['body'] === 'Hello Foo!' && $argument[4]['slug'] === 'second' &&
                $argument[4]['description_at_the_letter'] === 1 && $argument[5]['body'] === 'Hello Bar!' &&
                $argument[5]['slug'] === 'second' && $argument[5]['description_at_the_letter'] === 1 &&
                $argument[6]['body'] === 'Hello Foo!' && $argument[6]['slug'] === 'second' &&
                $argument[6]['description_at_the_letter'] === 2 && $argument[7]['body'] === 'Hello Bar!' &&
                $argument[7]['slug'] === 'second' && $argument[7]['description_at_the_letter'] === 2;
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
