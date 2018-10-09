<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Algolia\ScoutExtended\Settings\Synchronizer;

final class OptimizeCommandTest extends TestCase
{
    /**
     * @expectedException \Tests\Features\FakeException
     */
    public function testModelsAreFound(): void
    {
        $synchronizerMock = mock(Synchronizer::class);
        $synchronizerMock->shouldReceive('analyse')->with($this->mockIndex(User::class))->andThrow(FakeException::class);
        $this->swap(Synchronizer::class, $synchronizerMock);

        Artisan::call('scout:optimize', ['searchable' => User::class]);
    }

    public function testCreationOfLocalSettings(): void
    {
        $this->mockIndex(User::class, $this->defaults());

        Artisan::call('scout:optimize', ['searchable' => User::class]);

        $this->assertLocalHas($this->local());
    }
}
