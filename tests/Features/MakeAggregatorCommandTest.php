<?php

declare(strict_types=1);

namespace Tests\Features;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class MakeAggregatorCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        @unlink(app_path('Search/News.php'));
        @unlink(app_path('Search/Forum.php'));
    }

    public function tearDown(): void
    {
        @unlink(app_path('Search/News.php'));
        @unlink(app_path('Search/Forum.php'));

        parent::tearDown();
    }

    public function testCreatesAggregator(): void
    {
        Artisan::call('scout:make-aggregator', ['name' => 'News']);
        Artisan::call('scout:make-aggregator', ['name' => 'Forum']);

        $this->assertFileExists(app_path('Search/News.php'));
        $this->assertFileExists(app_path('Search/Forum.php'));
    }
}
