<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

final class OptimizeCommandTest extends TestCase
{
    public function testCreationOfLocalSettings(): void
    {
        factory(User::class)->create();

        $this->mockIndex(User::class, $this->defaults());

        Artisan::call('scout:optimize', ['searchable' => User::class]);

        $this->assertLocalHas($this->local());
    }

    public function testThatRequiresARowOnTheDatabase(): void
    {
        $this->mockIndex(User::class, $this->defaults());

        Artisan::call('scout:optimize', ['searchable' => User::class]);

        $this->assertFileNotExists(config_path('scout-users.php'));
    }
}
