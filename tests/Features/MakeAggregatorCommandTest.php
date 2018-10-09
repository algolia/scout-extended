<?php

declare(strict_types=1);

namespace Tests\Features;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

final class AggregatorCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        @unlink(app_path('Search/News.php'));
        @unlink(app_path('Search/Forum.php'));
    }

    public function tearDown()
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
