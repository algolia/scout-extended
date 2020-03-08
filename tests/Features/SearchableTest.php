<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\ScoutExtended\Searchable\ModelsResolver;
use App\EmptyItem;
use App\User;
use Illuminate\Support\Arr;
use Mockery;
use Tests\TestCase;

final class SearchableTest extends TestCase
{
    public function testSearchable(): void
    {
        $user = factory(User::class)->create();

        $user->withScoutMetaData('_rankingInfo', []);
        $user->withScoutMetaData('_highlightResult', []);

        $metadataKeys = ModelsResolver::$metadata;

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('saveObjects')->once()->with(Mockery::on(function ($argument) use ($metadataKeys) {
            return count(Arr::only($argument[0], $metadataKeys)) === 0;
        }));

        $user->searchable();
    }

    public function testSearchableWithEmptySearchableArray(): void
    {
        $item = new EmptyItem([
            'id' => 1,
            'title' => 'Example Title',
        ]);

        $item->pushSoftDeleteMetadata();

        $itemsIndex = $this->mockIndex(EmptyItem::class);
        $itemsIndex->expects('saveObjects')->once()->with([]);

        $item->searchable();
    }
}
