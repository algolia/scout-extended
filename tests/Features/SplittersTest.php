<?php

declare(strict_types=1);

namespace Tests\Features;

use Mockery;
use App\Thread;
use function count;
use Tests\TestCase;
use Algolia\ScoutExtended\Splitters\HtmlSplitter;

final class SplittersTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(database_path('migrations'));
    }

    public function testRecordsAreSplittedByASplitter(): void
    {
        $index = $this->mockIndex(ThreadWithSplitterClass::class);

        $index->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 2 &&
                $argument[0]['objectID'] === 'Tests\Features\ThreadWithSplitterClass::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\ThreadWithSplitterClass::1::1' &&
                $argument[0]['body'] === 'Hello Foo!' && $argument[1]['body'] === 'Hello Bar!';
        }))->andReturn($this->mockResponse());

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
                $argument[0]['objectID'] === 'Tests\Features\ThreadWithValueReturned::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\ThreadWithValueReturned::1::1' &&
                $argument[0]['body'] === 'Hello Foo!' && $argument[1]['body'] === 'Hello Bar!';
        }))->andReturn($this->mockResponse());

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
                $argument[0]['objectID'] === 'Tests\Features\ThreadWithSplitterInstance::1::0' &&
                $argument[1]['objectID'] === 'Tests\Features\ThreadWithSplitterInstance::1::1' &&
                $argument[0]['body'] === 'Hello Foo!' && $argument[1]['body'] === 'Hello Bar!';
        }))->andReturn($this->mockResponse());

        $body = implode('', [
            '<h1>Hello <strong>Foo!</strong></h1>',
            '<h1>Hello <strong>Bar</strong>!</h1>',
        ]);

        ThreadWithSplitterInstance::create(['body' => $body]);
    }

    public function testSearchMethod(): void
    {
        $index = $this->mockIndex(ThreadWithValueReturned::class);

        $index->shouldReceive('saveObjects')->once();

        $body = implode('', [
            '<h1>Hello <strong>Foo!</strong></h1>',
            '<h1>Hello <strong>Bar</strong>!</h1>',
        ]);

        ThreadWithValueReturned::create(['body' => $body]);

        $index->shouldReceive('search')->once()->andReturn([
            'hits' => [
                [
                    'body' => 'Hello Foo!',
                    'id' => 1,
                    'objectID' => "Tests\Features\ThreadWithValueReturned::1::0",
                ],
                [
                    'body' => 'Hello Bar!',
                    'id' => 1,
                    'objectID' => "Tests\Features\ThreadWithValueReturned::1::1",
                ],
                [
                    'body' => 'Hello John!',
                    'id' => 2,
                    'objectID' => "Tests\Features\ThreadWithValueReturned::2::0",
                ],
            ],
        ]);
        $models = ThreadWithValueReturned::search('Hello')->get();
        $this->assertEquals(2, $models->count());
        $this->assertInstanceOf(ThreadWithValueReturned::class, $models[0]);
        $this->assertInstanceOf(ThreadWithValueReturned::class, $models[1]);
    }
}

class ThreadWithSplitterClass extends Thread
{
    protected $table = 'threads';

    public function splitBody($value): string
    {
        return HtmlSplitter::class;
    }
}

class ThreadWithSplitterInstance extends Thread
{
    protected $table = 'threads';

    public function splitBody($value)
    {
        return HtmlSplitter::by('h1');
    }
}

class ThreadWithValueReturned extends Thread
{
    protected $table = 'threads';

    public function splitBody($value): array
    {
        return explode(',', $value);
    }
}

class ThreadText extends Thread
{
    protected $table = 'threads';

    public function splitBody($value): array
    {
        return explode(',', $value);
    }
}
