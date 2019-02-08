<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Tests\Features\Fixtures\FakeException;
use Algolia\ScoutExtended\Settings\Synchronizer;

final class SyncCommandTest extends TestCase
{
    /**
     * @expectedException \Tests\Features\Fixtures\FakeException
     */
    public function testModelsAreDiscovered(): void
    {
        $synchronizerMock = mock(Synchronizer::class);
        $synchronizerMock->shouldReceive('analyse')->once()->with($this->mockIndex(User::class))->andThrow(FakeException::class);
        $this->swap(Synchronizer::class, $synchronizerMock);

        Artisan::call('scout:sync', ['searchable' => User::class]);
    }

    public function testWhenLocalSettingsNotFoundWithOptimize(): void
    {
        factory(User::class)->create();

        $this->mockIndex(User::class, array_merge($this->defaults(), $this->local()));

        $this->artisan('scout:sync', ['searchable' => User::class])->expectsQuestion('Wish to optimize the search experience based on information from the searchable class?', true);

        $this->assertLocalHas($this->local());
    }

    public function testWhenLocalSettingsNotFoundWithoutOptimize(): void
    {
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $this->local()));

        $this->assertSettingsSet($usersIndex, [], ['settingsHash' => $this->localMd5()]);

        $this->artisan('scout:sync', ['searchable' => User::class])->expectsQuestion('Wish to optimize the search experience based on information from the searchable class?', false);

        $this->assertLocalHas($this->local());
    }

    public function testWhenRemoteSettingsNotFound(): void
    {
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($this->local(), true).';');

        $usersIndex = $this->mockIndex(User::class, $this->defaults());

        $this->assertSettingsSet($usersIndex, $this->local(), ['settingsHash' => $this->localMd5()]);

        Artisan::call('scout:sync', ['searchable' => User::class]);
    }

    public function testWhenSettingsAreTheSame(): void
    {
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($this->local(), true).';');

        $this->mockIndex(User::class, array_merge($this->defaults(), $this->local()), ['settingsHash' => $this->localMd5()]);

        Artisan::call('scout:sync', ['searchable' => User::class]);
    }

    public function testWhenLocalSettingsAreMostRecent(): void
    {
        $local = array_merge($this->local(), ['newSetting' => true]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($local, true).';');

        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $this->local()), [
            'settingsHash' => $this->localMd5(),
        ]);

        ksort($local);

        $this->assertSettingsSet($usersIndex, $local, ['settingsHash' => md5(serialize($local))]);

        Artisan::call('scout:sync', ['searchable' => User::class, '--no-interaction' => true]);
    }

    public function testWhenRemoteSettingsAreMostRecent(): void
    {
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($this->local(), true).';');

        $remoteSettings = array_merge($this->local(), ['newSetting' => true]);
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $remoteSettings), [
            'settingsHash' => $this->localMd5(),
        ]);

        ksort($remoteSettings);

        $this->assertSettingsSet($usersIndex, [], ['settingsHash' => md5(serialize($remoteSettings))]);

        Artisan::call('scout:sync', ['searchable' => User::class, '--no-interaction' => true]);
        $this->assertLocalHas($remoteSettings);
    }

    public function testWhenBothSettingsAreMostRecentAndNoneGotChosen(): void
    {
        $localSettings = array_merge($this->local(), ['newSetting' => false]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $remoteWithoutDefaults = array_merge($this->local(), ['newSetting' => true]);

        $this->mockIndex(User::class, array_merge($this->defaults(), $remoteWithoutDefaults), [
            'settingsHash' => $this->localMd5(),
        ]);

        Artisan::call('scout:sync', ['searchable' => User::class, '--no-interaction' => true, '--keep' => 'none']);

        $this->assertLocalHas($localSettings);
    }

    public function testWhenBothSettingsAreMostRecentAndLocalGotChosen(): void
    {
        $localSettings = array_merge($this->local(), ['newSetting' => false]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $remoteWithoutDefaults = array_merge($this->local(), ['newSetting' => true]);
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $remoteWithoutDefaults), [
            'settingsHash' => $this->localMd5(),
        ]);

        ksort($localSettings);

        $this->assertSettingsSet($usersIndex, $localSettings, ['settingsHash' => md5(serialize($localSettings))]);

        Artisan::call('scout:sync', ['searchable' => User::class, '--no-interaction' => true, '--keep' => 'local']);

        $this->assertLocalHas($localSettings);
    }

    public function testWhenBothSettingsAreMostRecentAndRemoteGotChosen(): void
    {
        $localSettings = array_merge($this->local(), ['newSetting' => false]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $remoteWithoutDefaults = array_merge($this->local(), ['newSetting' => true]);
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $remoteWithoutDefaults), [
            'settingsHash' => $this->localMd5(),
        ]);

        ksort($remoteWithoutDefaults);

        $this->assertSettingsSet($usersIndex, [], ['settingsHash' => md5(serialize($remoteWithoutDefaults))]);

        Artisan::call('scout:sync', ['searchable' => User::class, '--no-interaction' => true, '--keep' => 'remote']);

        $this->assertLocalHas($remoteWithoutDefaults);
    }

    public function testSynchronizingSettingsWithCustomPrefix(): void
    {
        $customSettings = array_merge($this->local(), ['customSetting' => true]);
        file_put_contents(config_path('scout-custom-users.php'), '<?php return '.var_export($customSettings, true).';');

        $prefix = config('scout.prefix');
        config(['scout.prefix' => 'custom_']);
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $customSettings), [
            'settingsHash' => $this->localMd5(),
        ]);
        config(['scout.prefix' => $prefix]);

        ksort($customSettings);

        $this->assertSettingsSet($usersIndex, $customSettings, ['settingsHash' => md5(serialize($customSettings))]);

        Artisan::call('scout:sync', ['searchable' => User::class, '--no-interaction' => true, '--keep' => 'local', '--prefix' => 'custom_']);

        $this->assertLocalHas($customSettings, config_path('scout-custom-users.php'));

        unlink(config_path('scout-custom-users.php'));
    }
}
