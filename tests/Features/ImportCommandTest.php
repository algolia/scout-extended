<?php

declare(strict_types=1);

namespace Tests\Features;

use App\EmptyItem;
use App\News;
use App\Thread;
use App\User;
use App\Wall;
use function count;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

final class ImportCommandTest extends TestCase
{
    public function testImport(): void
    {
        Wall::bootSearchable();
        News::bootSearchable();

        $this->app['config']->set('scout.soft_delete', true);

        factory(User::class, 5)->create();
        factory(EmptyItem::class, 2)->create();

        // Detects searchable models.
        $userIndexMock = $this->mockIndex(User::class);
        $userIndexMock->expects('clearObjects')->once();
        $userIndexMock->expects('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 5 && $argument[0]['objectID'] === 'App\User::1';
        }));

        // Detects aggregators.
        $wallIndexMock = $this->mockIndex(Wall::class);
        $wallIndexMock->expects('clearObjects')->once();
        $wallIndexMock->expects('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 5 && $argument[0]['objectID'] === 'App\User::1';
        }));

        $newsIndexMock = $this->mockIndex(News::class);
        $newsIndexMock->expects('clearObjects')->once();
        $newsIndexMock->expects('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 5 && $argument[0]['objectID'] === 'App\User::1';
        }));

        // Detects searchable models.
        $threadIndexMock = $this->mockIndex(Thread::class);
        $threadIndexMock->expects('clearObjects')->once();

        // Detects searchable models.
        $emptyItemIndexMock = $this->mockIndex(EmptyItem::class);
        $emptyItemIndexMock->expects('clearObjects')->once();
        $emptyItemIndexMock->expects('saveObjects')->once()->with([]);

        Artisan::call('scout:import');
    }
}
