<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;

final class FlushCommandTest extends TestCase
{
    public function testClearsIndex(): void
    {
        $this->mockIndex($class = User::class)->expects('clear')->once();

        $this->artisan('scout:flush', ['model' => User::class]);
    }
}
