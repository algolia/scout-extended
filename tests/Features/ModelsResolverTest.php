<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;
use Laravel\Scout\Builder;
use Illuminate\Database\Eloquent\Collection;
use Algolia\ScoutExtended\Searchable\ModelsResolver;

final class ModelsResolverTest extends TestCase
{
    public function testItResolvesWithMetaData(): void
    {
        $searchable = factory(User::class)->create();

        $scoutModels = (new ModelsResolver)->from(mock(Builder::class), $searchable, [
            'hits' => [
                [
                    'objectID' => 'App\User::1',
                    '_highlightResult' => [],
                    '_rankingInfo' => []
                ]
            ],
        ]);

        $scoutMetaData = $scoutModels->first()->scoutMetaData();

        $this->assertArrayHasKey('_highlightResult', $scoutMetaData);
        $this->assertArrayHasKey('_rankingInfo', $scoutMetaData);
    }
}
