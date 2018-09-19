<?php

declare(strict_types=1);

namespace Tests\Integration;

use UserSeeder;
use Tests\Models\User;
use Tests\TestCase as BaseTestCase;
use Algolia\LaravelScoutExtended\Facades\Algolia;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->refreshApplication();

        /**
         * Runs database migrations and creates an index in Algolia.
         */
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->withFactories(__DIR__.'/database/factories');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
        $this->seed(UserSeeder::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->refreshApplication();

        Algolia::index(User::class)->clear();
    }
}
