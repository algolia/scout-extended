<?php

declare(strict_types=1);

namespace Tests\Features;

use App\Thread;
use Tests\TestCase;

final class SearchTest extends TestCase
{
    public function testSearchEmpty(): void
    {
        $threadIndexMock = $this->mockIndex(Thread::class);

        $threadIndexMock->shouldReceive('search')->once()->andReturn([
            'hits' => [],
        ]);

        $models = Thread::search('input')->get();
        /* @var $models \Illuminate\Database\Eloquent\Collection */
        $this->assertCount(0, $models);
    }

    public function testSearchOrder(): void
    {
        $threadIndexMock = $this->mockIndex(Thread::class);

        $threadIndexMock->shouldReceive('saveObjects')->times(3);
        $threadIndexMock->shouldReceive('search')->once()->andReturn([
            'hits' => [
                ['objectID' => 'App\Thread::3'],
                ['objectID' => 'App\Thread::1'],
                ['objectID' => 'App\Thread::2'],
            ],
        ]);

        $threads = factory(Thread::class, 3)->create();

        $models = Thread::search('input')->get();
        /* @var $models \Illuminate\Database\Eloquent\Collection */
        $this->assertCount(3, $models);

        $this->assertInstanceOf(Thread::class, $models->get(0));
        $this->assertEquals($threads[2]->subject, $models->get(0)->subject);
        $this->assertEquals($threads[2]->id, $models->get(0)->id);

        $this->assertInstanceOf(Thread::class, $models->get(1));
        $this->assertEquals($threads[0]->subject, $models->get(1)->subject);
        $this->assertEquals($threads[0]->id, $models->get(1)->id);

        $this->assertInstanceOf(Thread::class, $models->get(2));
        $this->assertEquals($threads[1]->subject, $models->get(2)->subject);
        $this->assertEquals($threads[1]->id, $models->get(2)->id);
    }

    /**
     * @expectedException \Algolia\ScoutExtended\Exceptions\ShouldReimportSearchableException
     */
    public function testInvalidObjectId(): void
    {
        $threadIndexMock = $this->mockIndex(Thread::class);

        $threadIndexMock->shouldReceive('search')->once()->andReturn([
            'hits' => [
                ['objectID' => '1'],
            ],
        ]);

        Thread::search('input')->get();
    }
}
