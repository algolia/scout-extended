<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use App\Wall;
use App\Thread;
use Tests\TestCase;

final class FlushCommandTest extends TestCase
{
    public function testClearsIndex(): void
    {
        $this->mockIndex(User::class)->expects('clear')->once();
        $this->mockIndex(Thread::class)->expects('clear')->once();
        $this->mockIndex(Wall::class)->expects('clear')->once();

        /*
         * Detects searchable models.
         */
        $this->artisan('scout:flush');
    }
}
