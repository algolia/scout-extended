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

    public function testCreationOfLocalSettingsWithCustomPrefix(): void
    {
        factory(User::class)->create();

        $prefix = config('scout.prefix');
        config(['scout.prefix' => 'custom_']);
        $this->mockIndex(User::class, $this->defaults());
        config(['scout.prefix' => $prefix]);

        Artisan::call('scout:optimize', ['searchable' => User::class, '--prefix' => 'custom_']);

        $this->assertLocalHas($this->local(), config_path('scout-custom-users.php'));

        unlink(config_path('scout-custom-users.php'));
    }
}
