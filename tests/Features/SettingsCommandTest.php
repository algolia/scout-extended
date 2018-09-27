<?php

declare(strict_types=1);

namespace Tests\Features;

use Mockery;
use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Foundation\Application;
use Algolia\LaravelScoutExtended\Settings\Compiler;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\LaravelScoutExtended\Settings\Synchronizer;

final class SettingsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        @unlink(__DIR__.'/../config/scout-users.php');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        @unlink(__DIR__.'/../config/scout-users.php');
    }

    /**
     * @expectedException \Tests\Features\FakeException
     */
    public function testModelsAreFound(): void
    {
        $appMock = Mockery::mock(Application::class)->makePartial();
        $appMock->expects('getNamespace')->once()->andReturn('Tests\Models');
        $appMock->expects('path')->once()->andReturn(__DIR__.'/../');

        $this->swap(Application::class, $appMock);

        $synchronizerMock = Mockery::mock(Synchronizer::class);
        $synchronizerMock->shouldReceive('analyse')->with($this->mockIndex(User::class))->andThrow(FakeException::class);
        $this->swap(Synchronizer::class, $synchronizerMock);

        $this->artisan('scout:settings')->run();
    }

    public function testWhenLocalSettingsNotFound(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = $this->getLocalSettings();

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getSettings')->twice()->andReturn(array_merge($defaults, [
            'searchableAttributes' => ['unordered(foo)', 'bar'],
            'foo' => 'bar',
        ]));
        $usersIndex->expects('getIndexName')->twice()->andReturn((new User())->searchableAs());
        $usersIndex->expects('setSettings')->once()->with([
            'userData' => $this->getLocalSettingsMd5(),
        ]);

        $this->artisan('scout:settings', ['model' => User::class])->run();

        $this->assertFileExists(config_path('scout-users.php'));
        $this->assertEquals($localSettings, require config_path('scout-users.php'));
    }

    public function testWhenRemoteSettingsNotFound(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = $this->getLocalSettings();
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getSettings')->once()->andReturn($defaults);
        $usersIndex->expects('getIndexName')->twice()->andReturn((new User())->searchableAs());
        $usersIndex->expects('setSettings')->once()->with(array_merge($localSettings, [
            'userData' => $this->getLocalSettingsMd5(),
        ]));

        $this->artisan('scout:settings', ['model' => User::class])->run();
    }

    public function testWhenSettingsAreTheSame(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = $this->getLocalSettings();
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getIndexName')->once()->andReturn((new User())->searchableAs());
        $usersIndex->expects('getSettings')->once()->andReturn(array_merge($defaults, $this->getLocalSettings(), [
            'userData' => $this->getLocalSettingsMd5(),
        ]));
        $this->artisan('scout:settings', ['model' => User::class])->run();
    }

    public function testWhenLocalSettingsAreMostRecent(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = array_merge($this->getLocalSettings(), [
            'newSetting' => true,
        ]);
        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getIndexName')->twice()->andReturn((new User())->searchableAs());
        $usersIndex->expects('getSettings')->once()->andReturn(array_merge($defaults, $this->getLocalSettings(), [
            'userData' => $this->getLocalSettingsMd5(),
        ]));

        ksort($localSettings);

        $usersIndex->expects('setSettings')->once()->with(array_merge($localSettings, [
            'userData' => md5(serialize($localSettings)),
        ]));

        $this->artisan('scout:settings', ['model' => User::class, '--no-interaction' => true])->run();
    }

    public function testWhenRemoteSettingsAreMostRecent(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = $this->getLocalSettings();
        $remoteWithoutDefaults = array_merge($this->getLocalSettings(), ['newSetting' => true,]);

        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getIndexName')->twice()->andReturn((new User())->searchableAs());
        $usersIndex->expects('getSettings')->twice()->andReturn(array_merge($defaults, $remoteWithoutDefaults, [
            'userData' => $this->getLocalSettingsMd5(),
        ]));

        ksort($remoteWithoutDefaults);

        $usersIndex->expects('setSettings')->once()->with([
            'userData' => md5(serialize($remoteWithoutDefaults)),
        ]);

        $this->artisan('scout:settings', ['model' => User::class, '--no-interaction' => true])->run();
        $this->assertEquals($remoteWithoutDefaults, require config_path('scout-users.php'));
    }

    public function testWhenBothSettingsAreMostRecentAndNoneGotChosen(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = array_merge($this->getLocalSettings(), ['newSetting' => false]);
        $remoteWithoutDefaults = array_merge($this->getLocalSettings(), ['newSetting' => true,]);

        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getIndexName')->once()->andReturn((new User())->searchableAs());
        $usersIndex->expects('getSettings')->once()->andReturn(array_merge($defaults, $remoteWithoutDefaults, [
            'userData' => $this->getLocalSettingsMd5(),
        ]));

        $this->artisan('scout:settings', [
            'model' => User::class,
            '--no-interaction' => true,
            '--keep' => 'none',
        ])->run();
        $this->assertEquals($localSettings, require config_path('scout-users.php'));
    }

    public function testWhenBothSettingsAreMostRecentAndLocalGotChosen(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = array_merge($this->getLocalSettings(), ['newSetting' => false]);
        $remoteWithoutDefaults = array_merge($this->getLocalSettings(), ['newSetting' => true,]);

        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getIndexName')->twice()->andReturn((new User())->searchableAs());
        $usersIndex->expects('getSettings')->once()->andReturn(array_merge($defaults, $remoteWithoutDefaults, [
            'userData' => $this->getLocalSettingsMd5(),
        ]));

        ksort($localSettings);

        $usersIndex->expects('setSettings')->once()->with(array_merge($localSettings, [
            'userData' => md5(serialize($localSettings)),
        ]));

        $this->artisan('scout:settings', [
            'model' => User::class,
            '--no-interaction' => true,
            '--keep' => 'local',
        ])->run();
        $this->assertEquals($localSettings, require config_path('scout-users.php'));
    }

    public function testWhenBothSettingsAreMostRecentAndRemoteGotChosen(): void
    {
        $defaults = $this->getRemoteDefaultSettings();
        $localSettings = array_merge($this->getLocalSettings(), ['newSetting' => false]);
        $remoteWithoutDefaults = array_merge($this->getLocalSettings(), ['newSetting' => true,]);

        file_put_contents(config_path('scout-users.php'), '<?php return '.var_export($localSettings, true).';');

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getIndexName')->twice()->andReturn((new User())->searchableAs());
        $usersIndex->expects('getSettings')->twice()->andReturn(array_merge($defaults, $remoteWithoutDefaults, [
            'userData' => $this->getLocalSettingsMd5(),
        ]));

        ksort($remoteWithoutDefaults);

        $usersIndex->expects('setSettings')->once()->with([
            'userData' => md5(serialize($remoteWithoutDefaults)),
        ]);

        $this->artisan('scout:settings', [
            'model' => User::class,
            '--no-interaction' => true,
            '--keep' => 'remote',
        ])->run();
        $this->assertEquals($remoteWithoutDefaults, require config_path('scout-users.php'));
    }

    private function getRemoteDefaultSettings(): array
    {
        $defaultsIndex = $this->mockIndex('temp-laravel-scout-extended');
        $defaults = require __DIR__.'/../resources/defaults.php';
        $defaultsIndex->expects('getSettings')->once()->andThrow(NotFoundException::class);
        $defaultsIndex->expects('saveObject')->once();
        $defaultsIndex->expects('getSettings')->once()->andReturn(require __DIR__.'/../resources/defaults.php');
        $defaultsIndex->expects('clear')->once();
        $this->app->get(ClientInterface::class)->expects('deleteIndex')->with('temp-laravel-scout-extended')->once();

        return $defaults;
    }

    private function getLocalSettings(): array
    {
        $viewVariables = array_fill_keys(Compiler::getViewVariables(), null);

        return array_merge($viewVariables, [
            'searchableAttributes' => [
                'unordered(foo)',
                'bar',
            ],
            'foo' => 'bar',
        ]);
    }

    private function getLocalSettingsMd5(): string
    {
        $content = $this->getLocalSettings();

        ksort($content);

        return md5(serialize($content));
    }
}

class FakeException extends \Exception
{
}
