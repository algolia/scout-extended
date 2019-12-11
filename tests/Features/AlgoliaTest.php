<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\AlgoliaSearch\AnalyticsClient;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\ScoutExtended\Algolia;
use App\User;
use Tests\TestCase;

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
        $this->assertInstanceOf(SearchIndex::class, $index = $this->algolia->index(User::class));

        $index = $this->algolia->index($model = new User);
        $this->assertInstanceOf(SearchIndex::class, $index);
        $this->assertSame($model->searchableAs(), $index->getIndexName());
    }

    public function testClientGetter(): void
    {
        $this->assertInstanceOf(SearchClient::class, $this->algolia->client());
    }

    public function testAnalyticsGetter(): void
    {
        $this->assertInstanceOf(AnalyticsClient::class, $this->algolia->analytics());
    }
}
