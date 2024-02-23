<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;

class UnsearchableTest extends TestCase
{
    public function testUnsearchable(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        factory(User::class, 5)->create();

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\User::1', 'App\User::2', 'App\User::3', 'App\User::4', 'App\User::5'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\User::1'],
            ['objectID' => 'App\User::2'],
            ['objectID' => 'App\User::3'],
            ['objectID' => 'App\User::4'],
            ['objectID' => 'App\User::5'],
        ]);
        $usersIndex->shouldReceive('deleteObjects')->once()->with([
            'App\User::1', 'App\User::2', 'App\User::3', 'App\User::4', 'App\User::5'
        ]);

        User::get()->unsearchable();
    }

    public function testUnsearchableWithDeprecatedDeleteBy(): void
    {
        factory(User::class, 5)->create();

        $usersIndex = $this->mockIndex(User::class);
        $usersIndex->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\User::1', 'App\User::2', 'App\User::3', 'App\User::4', 'App\User::5'],
            ],
        ]);

        User::get()->unsearchable();
    }
}
