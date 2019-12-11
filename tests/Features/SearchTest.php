<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\ScoutExtended\Exceptions\ShouldReimportSearchableException;
use App\Thread;
use App\User;
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
        $this->assertSame($threads[2]->subject, $models->get(0)->subject);
        $this->assertSame($threads[2]->id, $models->get(0)->id);

        $this->assertInstanceOf(Thread::class, $models->get(1));
        $this->assertSame($threads[0]->subject, $models->get(1)->subject);
        $this->assertSame($threads[0]->id, $models->get(1)->id);

        $this->assertInstanceOf(Thread::class, $models->get(2));
        $this->assertSame($threads[1]->subject, $models->get(2)->subject);
        $this->assertSame($threads[1]->id, $models->get(2)->id);
    }

    public function testInvalidObjectId(): void
    {
        $this->expectException(ShouldReimportSearchableException::class);

        $threadIndexMock = $this->mockIndex(Thread::class);

        $threadIndexMock->shouldReceive('search')->once()->andReturn([
            'hits' => [
                ['objectID' => '1'],
            ],
        ]);

        Thread::search('input')->get();
    }

    public function testSearchContainsMetadata(): void
    {
        $indexMock = $this->mockIndex(User::class);
        $indexMock->expects('saveObjects')->once();
        $indexMock->shouldReceive('search')->once()->andReturn([
            'hits' => [
                [
                    'objectID' => 'App\User::1',
                    '_highlightResult' => [],
                    '_rankingInfo' => [],
                ],
            ],
        ]);

        factory(User::class)->create();

        $scoutMetaData = User::search('')->get()->first()->scoutMetaData();

        $this->assertArrayHasKey('_highlightResult', $scoutMetaData);
        $this->assertArrayHasKey('_rankingInfo', $scoutMetaData);
    }
}
