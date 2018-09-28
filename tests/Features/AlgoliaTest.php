<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;
use Algolia\ScoutExtended\Algolia;
use Algolia\AlgoliaSearch\Analytics;
use Algolia\AlgoliaSearch\Interfaces\IndexInterface;
use Algolia\AlgoliaSearch\Interfaces\ClientInterface;

final class AlgoliaTest extends TestCase
{
    public $algolia;

    public function setUp(): void
    {
        parent::setUp();

        $this->algolia = resolve(Algolia::class);
    }

    public function testIndexGetter(): void
    {
        $this->assertInstanceOf(IndexInterface::class, $index = $this->algolia->index(User::class));

        $index = $this->algolia->index($model = new User);
        $this->assertInstanceOf(IndexInterface::class, $index);
        $this->assertEquals($model->searchableAs(), $index->getIndexName());
    }

    public function testClientGetter(): void
    {
        $this->assertInstanceOf(ClientInterface::class, $this->algolia->client());
    }

    public function testAnalyticsGetter(): void
    {
        $this->assertInstanceOf(Analytics::class, $this->algolia->analytics());
    }
}
