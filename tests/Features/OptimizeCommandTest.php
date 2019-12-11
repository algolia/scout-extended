<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class OptimizeCommandTest extends TestCase
{
    public function testCreationOfLocalSettings(): void
    {
        factory(User::class)->create();

        $this->mockIndex(User::class, $this->defaults());

        Artisan::call('scout:optimize', ['searchable' => User::class]);

        $this->assertLocalHas($this->local());
    }

    public function testCreationOfLocalSettingsWithCustomPath(): void
    {
        config(['scout.algolia.settings_path' => resource_path('algolia')]);

        factory(User::class)->create();

        $this->mockIndex(User::class, $this->defaults());

        Artisan::call('scout:optimize', ['searchable' => User::class, '--no-interaction']);

        $this->assertLocalHas($this->local(), resource_path('algolia/scout-users.php'));

        unlink(resource_path('algolia/scout-users.php'));
        rmdir(resource_path('algolia'));
    }

    public function testThatRequiresARowOnTheDatabase(): void
    {
        $this->mockIndex(User::class, $this->defaults());

        Artisan::call('scout:optimize', ['searchable' => User::class]);

        $this->assertFileNotExists(config_path('scout-users.php'));
    }
}
