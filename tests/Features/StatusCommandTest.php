<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

final class StatusCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(database_path('migrations'));
        $this->artisan('migrate:fresh', ['--database' => 'testbench'])->run();
    }

    public function testStatus(): void
    {
        $usersIndex = $this->mockIndex(User::class, $this->defaults());

        $usersIndex->shouldReceive('search')->andReturn([
            'nbHits' => 0,
        ]);

        Artisan::call('scout:status', ['searchable' => User::class]);
    }
}
