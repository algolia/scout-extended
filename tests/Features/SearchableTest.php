<?php

declare (strict_types = 1);

namespace Tests\Features;

use Mockery;
use App\User;
use Tests\TestCase;

final class SearchableTest extends TestCase
{
    public function testSearchable(): void
    {
        $user = factory(User::class)->create();

        $user->withScoutMetaData('_rankingInfo', []);
        $user->withScoutMetaData('_highlightResult', []);

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->expects('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return !in_array('_rankingInfo', $argument[0]) && !in_array('_highlightResult', $argument[0]);
        }));

        $user->searchable();
    }
}
