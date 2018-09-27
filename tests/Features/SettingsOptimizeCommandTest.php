<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\AlgoliaSearch\Interfaces\ClientInterface;
use Algolia\LaravelScoutExtended\Settings\Compiler;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;
use Tests\Models\User;
use Illuminate\Foundation\Application;
use Algolia\LaravelScoutExtended\Settings\Synchronizer;

final class SettingsOptimizeCommandTest extends TestCase
{
    /**
     * @expectedException \Tests\Features\FakeException
     */
    public function testModelsAreFound(): void
    {
        $appMock = Mockery::mock($this->app)->makePartial();
        $appMock->expects('getNamespace')->once()->andReturn('Tests\Models');

        $this->swap(Application::class, $appMock);

        $synchronizerMock = Mockery::mock(Synchronizer::class);
        $synchronizerMock->shouldReceive('analyse')->with($this->mockIndex(User::class))->andThrow(FakeException::class);
        $this->swap(Synchronizer::class, $synchronizerMock);

        $this->artisan('scout:settings-optimize')->run();
    }

    public function testCreationOfLocalSettings(): void
    {
        $defaults = $this->getRemoteDefaultSettings();

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('getIndexName')->once()->andReturn((new User())->searchableAs());
        $usersIndex->expects('getSettings')->once()->andReturn($defaults);

        Artisan::call('scout:settings-optimize', ['model' => User::class]);

        $this->assertEquals($this->getLocalSettings(), require config_path('scout-users.php'));
        $this->assertFileExists(config_path('scout-users.php'));
    }

    private function getLocalSettings(): array
    {
        $viewVariables = array_fill_keys(Compiler::getViewVariables(), null);

        return array_merge($viewVariables, [
            'searchableAttributes' => [
                'name',
                'email',
            ],
            'queryLanguages' => ['en'],
        ]);
    }
}
