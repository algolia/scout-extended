<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Algolia\ScoutExtended\Settings\Synchronizer;

final class SyncCommandTest extends TestCase
{
    /**
     * @expectedException \Tests\Features\FakeException
     */
    public function testModelsAreDiscovered(): void
    {
        $synchronizerMock = mock(Synchronizer::class);
        $synchronizerMock->shouldReceive('analyse')->once()->with($this->mockIndex(User::class))->andThrow(FakeException::class);
        $this->swap(Synchronizer::class, $synchronizerMock);

        Artisan::call('scout:sync', ['model' => User::class]);
    }

    public function testWhenLocalSettingsNotFoundWithOptimize(): void
    {
        $this->mockIndex(User::class, array_merge($this->defaults(), $this->local()));

        $this->artisan('scout:sync', ['model' => User::class])->expectsQuestion('Wish to optimize the search experience based on information from your model class?', true);

        $this->assertLocalHas($this->local());
    }

    public function testWhenLocalSettingsNotFoundWithoutOptimize(): void
    {
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $this->local()));

        $this->assertSettingsSet($usersIndex, ['userData' => $this->localMd5()]);

        $this->artisan('scout:sync', ['model' => User::class])->expectsQuestion('Wish to optimize the search experience based on information from your model class?', false);

        $this->assertLocalHas($this->local());
    }

    public function testWhenRemoteSettingsNotFound(): void
    {
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($this->local(), true).';');

        $usersIndex = $this->mockIndex(User::class, $this->defaults());

        $this->assertSettingsSet($usersIndex, array_merge($this->local(), ['userData' => $this->localMd5()]));

        Artisan::call('scout:sync', ['model' => User::class]);
    }

    public function testWhenSettingsAreTheSame(): void
    {
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($this->local(), true).';');

        $this->mockIndex(User::class, array_merge($this->defaults(), $this->local(), ['userData' => $this->localMd5(),]));

        Artisan::call('scout:sync', ['model' => User::class]);
    }

    public function testWhenLocalSettingsAreMostRecent(): void
    {
        $local = array_merge($this->local(), ['newSetting' => true]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($local, true).';');

        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $this->local(), [
            'userData' => $this->localMd5(),
        ]));

        ksort($local);

        $this->assertSettingsSet($usersIndex, array_merge($local, ['userData' => md5(serialize($local)),]));

        Artisan::call('scout:sync', ['model' => User::class, '--no-interaction' => true]);
    }

    public function testWhenRemoteSettingsAreMostRecent(): void
    {
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($this->local(), true).';');

        $remoteSettings = array_merge($this->local(), ['newSetting' => true]);
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $remoteSettings, [
            'userData' => $this->localMd5(),
        ]));

        ksort($remoteSettings);

        $this->assertSettingsSet($usersIndex, ['userData' => md5(serialize($remoteSettings))]);

        Artisan::call('scout:sync', ['model' => User::class, '--no-interaction' => true]);
        $this->assertLocalHas($remoteSettings);
    }

    public function testWhenBothSettingsAreMostRecentAndNoneGotChosen(): void
    {
        $localSettings = array_merge($this->local(), ['newSetting' => false]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $remoteWithoutDefaults = array_merge($this->local(), ['newSetting' => true]);

        $this->mockIndex(User::class, array_merge($this->defaults(), $remoteWithoutDefaults, [
            'userData' => $this->localMd5(),
        ]));

        Artisan::call('scout:sync', ['model' => User::class, '--no-interaction' => true, '--keep' => 'none']);

        $this->assertLocalHas($localSettings);
    }

    public function testWhenBothSettingsAreMostRecentAndLocalGotChosen(): void
    {
        $localSettings = array_merge($this->local(), ['newSetting' => false]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $remoteWithoutDefaults = array_merge($this->local(), ['newSetting' => true]);
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $remoteWithoutDefaults, [
            'userData' => $this->localMd5(),
        ]));

        ksort($localSettings);

        $this->assertSettingsSet($usersIndex, array_merge($localSettings, ['userData' => md5(serialize($localSettings)),]));

        Artisan::call('scout:sync', ['model' => User::class, '--no-interaction' => true, '--keep' => 'local']);

        $this->assertLocalHas($localSettings);
    }

    public function testWhenBothSettingsAreMostRecentAndRemoteGotChosen(): void
    {
        $localSettings = array_merge($this->local(), ['newSetting' => false]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $remoteWithoutDefaults = array_merge($this->local(), ['newSetting' => true]);
        $usersIndex = $this->mockIndex(User::class, array_merge($this->defaults(), $remoteWithoutDefaults, [
            'userData' => $this->localMd5(),
        ]));

        ksort($remoteWithoutDefaults);

        $this->assertSettingsSet($usersIndex, ['userData' => md5(serialize($remoteWithoutDefaults)),]);

        Artisan::call('scout:sync', ['model' => User::class, '--no-interaction' => true, '--keep' => 'remote']);

        $this->assertLocalHas($remoteWithoutDefaults);
    }
}

class FakeException extends \Exception
{
}
