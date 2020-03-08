<?php

declare(strict_types=1);

namespace Tests\Features;

use App\EmptyItem;
use App\News;
use App\Thread;
use App\User;
use App\Wall;
use Tests\TestCase;

final class FlushCommandTest extends TestCase
{
    public function testClearsIndex(): void
    {
        $this->mockIndex(News::class)->expects('clearObjects')->once();
        $this->mockIndex(User::class)->expects('clearObjects')->once();
        $this->mockIndex(Thread::class)->expects('clearObjects')->once();
        $this->mockIndex(Wall::class)->expects('clearObjects')->once();
        $this->mockIndex(EmptyItem::class)->expects('clearObjects')->once();

        /*
         * Detects searchable models.
         */
        $this->artisan('scout:flush');
    }
}
