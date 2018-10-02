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

    public function testClearsIndex(): void
    {
        Artisan::call('scout:aggregator', ['name' => 'News']);
        Artisan::call('scout:aggregator', ['name' => 'Forum']);

        $this->assertFileExists(app_path('Search/News.php'));
        $this->assertFileExists(app_path('Search/Forum.php'));
    }
}
