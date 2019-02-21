<?php

declare(strict_types=1);

namespace Tests\Features;

use Mockery;
use App\User;
use Tests\TestCase;
use Illuminate\Support\Arr;
use Algolia\ScoutExtended\Searchable\ModelsResolver;

final class SearchableTest extends TestCase
{
    public function testSearchable(): void
    {
        $user = factory(User::class)->create();

        $user->withScoutMetaData('_rankingInfo', []);
        $user->withScoutMetaData('_highlightResult', []);

        $metadataKeys = ModelsResolver::$metadata;

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('saveObjects')->once()->with(Mockery::on(function ($argument) use($metadataKeys) {
            return count(Arr::only($argument[0], $metadataKeys)) === 0;
        }));

        $user->searchable();
    }
}
