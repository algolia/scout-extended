<?php

declare(strict_types=1);

namespace Tests\Features;

use App\Thread;
use App\User;
use Tests\Features\Fixtures\ThreadWithSplitterClass;
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

    public function testCreationOfLocalSettingsWithHtmlSplitter(): void
    {
        config(['scout.algolia.settings_path' => resource_path('algolia')]);

        factory(ThreadWithSplitterClass::class)->create();

        $this->mockIndex(ThreadWithSplitterClass::class, $this->defaults());

        Artisan::call('scout:optimize', ['searchable' => ThreadWithSplitterClass::class]);

        $this->assertLocalHas($this->localThread(), resource_path('algolia/scout-threads.php'));

        unlink(resource_path('algolia/scout-threads.php'));
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
