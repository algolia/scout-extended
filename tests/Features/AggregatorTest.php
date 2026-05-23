<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\Iterators\ObjectIterator;
use Algolia\ScoutExtended\Searchable\Aggregator;
use Algolia\ScoutExtended\Searchable\AggregatorCollection;
use Algolia\ScoutExtended\Searchable\AggregatorObserver;
use Algolia\ScoutExtended\Searchable\Aggregators;
use App\All;
use App\News;
use App\Post;
use App\Thread;
use App\User;
use App\Wall;
use Laravel\Scout\ModelObserver;
use Laravel\Scout\Scout;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AggregatorTest extends TestCase
{
    public function testWhenAggregagorIsNotBooted(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        $client = $this->mockIndex('users');

        $client->shouldReceive('saveObjects')->once()->with('users', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));

        $user = factory(User::class)->create();

        $client->shouldReceive('browseObjects')->once()->with('users', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\User::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\User::1']]);

        $client->shouldReceive('deleteObjects')->once()->with('users', ['App\User::1']);

        $user->delete();
    }

    public function testWhenAggregagorIsNotBootedWithDeprecatedDeleteBy(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', true);

        $client = $this->mockIndex('users');

        $client->shouldReceive('saveObjects')->once()->with('users', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));

        $user = factory(User::class)->create();

        $client->shouldReceive('deleteBy')->once()->with('users', [
            'tagFilters' => [['App\User::1']],
        ]);

        $user->delete();
    }

    public function testAggregatorWithSearchableModel(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Wall::bootSearchable();

        $this->mockIndex('users');
        $this->mockIndex('wall');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->once()->with('users', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $client->shouldReceive('saveObjects')->once()->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $client->shouldReceive('browseObjects')->once()->with('users', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\User::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\User::1']]);

        $client->shouldReceive('deleteObjects')->once()->with('users', ['App\User::1']);

        $client->shouldReceive('browseObjects')->once()->with('wall', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\User::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\User::1']]);

        $client->shouldReceive('deleteObjects')->once()->with('wall', ['App\User::1']);

        $user->delete();
    }

    public function testAggregatorWithSearchableModelWithDeprecatedDeleteBy(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', true);

        Wall::bootSearchable();

        $this->mockIndex('users');
        $this->mockIndex('wall');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->once()->with('users', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $client->shouldReceive('saveObjects')->once()->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $client->shouldReceive('deleteBy')->once()->with('users', [
            'tagFilters' => [['App\User::1']],
        ]);

        $client->shouldReceive('deleteBy')->once()->with('wall', [
            'tagFilters' => [['App\User::1']],
        ]);

        $user->delete();
    }

    public function testAggregatorWithNonSearchableModel(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Wall::bootSearchable();

        $this->mockIndex(Thread::class);
        $this->mockIndex('wall');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->once()->with('threads', Mockery::any());
        $client->shouldReceive('saveObjects')->once()->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('body', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Thread::1';
        }));
        $thread = factory(Thread::class)->create();

        $client->shouldReceive('browseObjects')->once()->with('threads', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\Thread::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\Thread::1']]);

        $client->shouldReceive('deleteObjects')->once()->with('threads', ['App\Thread::1']);

        $client->shouldReceive('browseObjects')->once()->with('wall', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\Thread::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\Thread::1']]);

        $client->shouldReceive('deleteObjects')->once()->with('wall', ['App\Thread::1']);

        $thread->delete();
    }

    public function testAggregatorWithNonSearchableModelWithDeprecatedDeleteBy(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', true);

        Wall::bootSearchable();

        $this->mockIndex(Thread::class);
        $this->mockIndex('wall');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->once()->with('threads', Mockery::any());
        $client->shouldReceive('saveObjects')->once()->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('body', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Thread::1';
        }));
        $thread = factory(Thread::class)->create();

        $client->shouldReceive('deleteBy')->once()->with('threads', [
            'tagFilters' => [['App\Thread::1']],
        ]);

        $client->shouldReceive('deleteBy')->once()->with('wall', [
            'tagFilters' => [['App\Thread::1']],
        ]);

        $thread->delete();
    }

    public function testAggregatorSoftDeleteModelWithoutSoftDeletesOnIndex(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Wall::bootSearchable();

        $client = $this->mockIndex('wall');

        // Laravel Scout restore calls twice the save objects.
        $client->shouldReceive('saveObjects')->times(3)->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $client->shouldReceive('browseObjects')->times(3)->with('wall', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\Post::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\Post::1']]);
        $client->shouldReceive('deleteObjects')->times(3)->with('wall', ['App\Post::1']);

        $post = factory(Post::class)->create();
        $post->delete();
        $post->restore();
        $post->forceDelete();
    }

    public function testAggregatorSoftDeleteModelWithoutSoftDeletesOnIndexWithDeprecatedDeleteBy(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', true);

        Wall::bootSearchable();

        $client = $this->mockIndex('wall');

        // Laravel Scout restore calls twice the save objects.
        $client->shouldReceive('saveObjects')->times(3)->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $client->shouldReceive('deleteBy')->times(3)->with('wall', [
            'tagFilters' => [['App\Post::1']],
        ]);

        $post = factory(Post::class)->create();
        $post->delete();
        $post->restore();
        $post->forceDelete();
    }

    public function testAggregatorSoftDeleteModelWithSoftDeletesOnIndex(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Wall::bootSearchable();

        $this->app['config']->set('scout.soft_delete', true);

        $client = $this->mockIndex('wall');

        // Laravel Scout force Delete calls once the save() method.
        $client->shouldReceive('saveObjects')->times(3)->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $post = factory(Post::class)->create();
        $post->delete();

        $client->shouldReceive('browseObjects')->once()->with('wall', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\Post::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\Post::1']]);
        $client->shouldReceive('deleteObjects')->once()->with('wall', ['App\Post::1']);

        $post->forceDelete();
    }

    public function testAggregatorSoftDeleteModelWithSoftDeletesOnIndexWithDeprecatedDeleteBy(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', true);

        Wall::bootSearchable();

        $this->app['config']->set('scout.soft_delete', true);

        $client = $this->mockIndex('wall');

        // Laravel Scout force Delete calls once the save() method.
        $client->shouldReceive('saveObjects')->times(3)->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $post = factory(Post::class)->create();
        $post->delete();

        $client->shouldReceive('deleteBy')->once()->with('wall', [
            'tagFilters' => [['App\Post::1']],
        ]);
        $post->forceDelete();
    }

    public function testAggregatorSearch(): void
    {
        Wall::bootSearchable();

        $this->mockIndex(Thread::class);
        $this->mockIndex('wall');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->with('threads', Mockery::any())->times(2);
        $client->shouldReceive('saveObjects')->with('wall', Mockery::any())->times(4);
        $client->shouldReceive('searchSingleIndex')->with('wall', Mockery::any())->once()->andReturn([
            'hits' => [
                ['objectID' => 'App\Post::1'],
                ['objectID' => 'App\Thread::1'],
                ['objectID' => 'App\Thread::2'],
                ['objectID' => 'App\Post::2'],
            ],
        ]);

        $post = factory(Post::class, 2)->create();
        $thread = factory(Thread::class, 2)->create();

        $models = Wall::search('input')->get();

        $this->assertCount(4, $models);

        $this->assertInstanceOf(Post::class, $models->get(0));
        $this->assertSame($post[0]->subject, $models->get(0)->subject);
        $this->assertSame($post[0]->id, $models->get(0)->id);

        $this->assertInstanceOf(Thread::class, $models->get(1));
        $this->assertSame($thread[0]->body, $models->get(1)->body);
        $this->assertSame($thread[0]->id, $models->get(1)->id);

        $this->assertInstanceOf(Thread::class, $models->get(2));
        $this->assertSame($thread[1]->body, $models->get(2)->body);
        $this->assertSame($thread[1]->id, $models->get(2)->id);

        $this->assertInstanceOf(Post::class, $models->get(3));
        $this->assertSame($post[1]->subject, $models->get(3)->subject);
        $this->assertSame($post[1]->id, $models->get(3)->id);
    }

    public function testSerializationOfCollection(): void
    {
        $aggregators = factory(Post::class, 100)->create()->map(function ($model) {
            return Wall::create(Post::find($model->id));
        })->toArray();

        $collection = AggregatorCollection::make($aggregators);

        $collectionQueued = unserialize(serialize(clone $collection));

        $this->assertSame(Wall::class, $collectionQueued->aggregator);
        $this->assertEquals($collection->toArray(), $collectionQueued->toArray());
    }

    public function testRelationLoad(): void
    {
        Wall::bootSearchable();
        News::bootSearchable();

        $this->mockIndex('users');
        $this->mockIndex('wall');
        $this->mockIndex('news');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects');

        $user = factory(User::class)->create();

        $response = ['hits' => [['objectID' => 'App\User::1']]];
        $client->shouldReceive('searchSingleIndex')->with('wall', Mockery::any())->once()->andReturn($response);
        $client->shouldReceive('searchSingleIndex')->with('news', Mockery::any())->once()->andReturn($response);

        $this->assertFalse(Wall::search()->get()->first()->relationLoaded('threads'));
        $this->assertTrue(News::search()->get()->first()->relationLoaded('threads'));
    }

    public function testAggregatorWithMultipleBoots(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Aggregators::bootSearchables([
            Wall::class,
            All::class,
        ]);

        $this->mockIndex('users');
        $this->mockIndex('wall');
        $this->mockIndex('all');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->once()->with('users', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $client->shouldReceive('saveObjects')->once()->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $client->shouldReceive('saveObjects')->once()->with('all', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $client->shouldReceive('browseObjects')->once()->with('users', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\User::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\User::1']]);
        $client->shouldReceive('deleteObjects')->once()->with('users', ['App\User::1']);

        $client->shouldReceive('browseObjects')->once()->with('wall', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\User::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\User::1']]);
        $client->shouldReceive('deleteObjects')->once()->with('wall', ['App\User::1']);

        $client->shouldReceive('browseObjects')->once()->with('all', [
            'attributesToRetrieve' => ['objectID'],
            'tagFilters' => [['App\User::1']],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([['objectID' => 'App\User::1']]);
        $client->shouldReceive('deleteObjects')->once()->with('all', ['App\User::1']);

        $user->delete();
    }

    public function testAggregatorWithMultipleBootsWithDeprecatedDeleteBy(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', true);

        Aggregators::bootSearchables([
            Wall::class,
            All::class,
        ]);

        $this->mockIndex('users');
        $this->mockIndex('wall');
        $this->mockIndex('all');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->once()->with('users', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $client->shouldReceive('saveObjects')->once()->with('wall', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $client->shouldReceive('saveObjects')->once()->with('all', Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $client->shouldReceive('deleteBy')->once()->with('users', ['tagFilters' => [['App\User::1']]]);
        $client->shouldReceive('deleteBy')->once()->with('wall', ['tagFilters' => [['App\User::1']]]);
        $client->shouldReceive('deleteBy')->once()->with('all', ['tagFilters' => [['App\User::1']]]);

        $user->delete();
    }

    public function testWhenAggregatorIsBootedBeforePlainScoutSearchableTrait(): void
    {
        $this->expectNotToPerformAssertions();

        config(['scout.queue' => true]);

        // Scout's base `queueRemoveFromSearch` method dispatches this job to remove models from
        // search, but Scout Extended's Aggregator class overrides that method, so this job
        // should never be dispatched for an Aggregator even when 'scout.queue' is true
        Scout::$removeFromSearchJob = DummyRemoveFromSearch::class;

        $user = factory(User::class)->create();

        // Boot the aggregator, which registers its own Collection macros, overriding Scout's
        All::bootSearchable();

        // Because the Thread model had not been booted yet, booting `All` above caused it to
        // boot, which in turn booted its Searchable trait and re-registered Scout's base
        // Collection macros, overriding the aggregator.

        // Calling `unsearchable` on an Aggregator should bypass these macros and still end
        // up calling `queueRemoveFromSearch` on the Aggregator, not dispatching any jobs.

        $user->delete();
    }

    public function testSkipDeletedWhenDisableSyncingFor(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        $this->mockIndex('wall');
        $this->mockIndex('users');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->with('users', Mockery::any())->once();
        $user = factory(User::class)->create();

        ModelObserver::disableSyncingFor(User::class);

        Wall::bootSearchable();

        try {
            $client->shouldReceive('browseObjects')->once()->with('wall', [
                'attributesToRetrieve' => ['objectID'],
                'tagFilters' => [['App\User::1']],
            ])->andReturn([['objectID' => 'App\User::1']]);
            $client->shouldReceive('deleteObjects')->once()->with('wall', ['App\User::1']);

            $client->shouldNotReceive('browseObjects')->with('users', Mockery::any());
            $client->shouldNotReceive('deleteObjects')->with('users', Mockery::any());

            $user->delete();
        } finally {
            ModelObserver::enableSyncingFor(User::class);
        }
    }

    public function testSkipForceDeletedWhenDisableSyncingFor(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        $this->mockIndex('wall');
        $this->mockIndex('users');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->with('users', Mockery::any())->once();
        $user = factory(User::class)->create();

        ModelObserver::disableSyncingFor(User::class);

        Wall::bootSearchable();

        try {
            $client->shouldReceive('browseObjects')->once()->with('wall', [
                'attributesToRetrieve' => ['objectID'],
                'tagFilters' => [['App\User::1']],
            ])->andReturn([['objectID' => 'App\User::1']]);
            $client->shouldReceive('deleteObjects')->once()->with('wall', ['App\User::1']);

            $client->shouldNotReceive('browseObjects')->with('users', Mockery::any());
            $client->shouldNotReceive('deleteObjects')->with('users', Mockery::any());

            $user->forceDelete();
        } finally {
            ModelObserver::enableSyncingFor(User::class);
        }
    }

    public function testSkipSavedWhenDisableSyncingFor(): void
    {
        $this->mockIndex('wall');
        $this->mockIndex('users');
        $client = $this->mockClient();

        $client->shouldReceive('saveObjects')->with('users', Mockery::any())->once();
        $user = factory(User::class)->create();

        ModelObserver::disableSyncingFor(User::class);

        try {
            $client->shouldReceive('saveObjects')->once()->with('wall', Mockery::on(function ($argument) {
                return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                    $argument[0]['objectID'] === 'App\User::1';
            }));

            $client->shouldNotReceive('saveObjects')->with('users', Mockery::on(function ($argument) {
                return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                    $argument[0]['objectID'] === 'App\User::1';
            }));

            Wall::bootSearchable();

            $user->save();
        } finally {
            ModelObserver::enableSyncingFor(User::class);
        }
    }

    public function testSkipSavedWhenDisableSyncingForAggregator(): void
    {
        $wallIndexMock = $this->mockIndex('wall');
        $usersIndexMock = $this->mockIndex('users');

        $usersIndexMock->shouldReceive('saveObjects')->once();

        $user = factory(User::class)->create();

        AggregatorObserver::disableSyncingFor(Wall::class);

        Wall::bootSearchable();
        try {
            // Ensure saveObjects is NOT called for the wall index (aggregator)
            $wallIndexMock->shouldNotReceive('saveObjects')
                ->with(Mockery::on(function ($argument) {
                    return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                        $argument[0]['objectID'] === 'App\User::1';
                }));

            // Expect saveObjects to be called once for the user index
            $usersIndexMock->shouldReceive('saveObjects')
                ->once()
                ->with(Mockery::on(function ($argument) {
                    return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                        $argument[0]['objectID'] === 'App\User::1';
                }));

            Wall::bootSearchable();

            $user->save();
        } finally {
            AggregatorObserver::enableSyncingFor(User::class);
        }
    }

    public function testSkipDeletedWhenDisableSyncingForAggregator(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        $wallIndexMock = $this->mockIndex('wall');
        $usersIndexMock = $this->mockIndex('users');

        $usersIndexMock->shouldReceive('saveObjects')->once();

        $user = factory(User::class)->create();

        AggregatorObserver::disableSyncingFor(Wall::class);

        Wall::bootSearchable();

        try {
            // Ensure saveObjects is NOT called for the wall index (aggregator)
            $wallIndexMock->shouldNotReceive('browseObjects')->with([
                'attributesToRetrieve' => ['objectID'],
                'tagFilters' => [['App\User::1']],
            ])->andReturn([['objectID' => 'App\User::1']]);
            $wallIndexMock->shouldNotReceive('deleteObjects')->with(['App\User::1']);

            // Expect browseObjects and deleteObjects to be called on the users index
            $usersIndexMock->shouldReceive('browseObjects')->once()->with([
                'attributesToRetrieve' => ['objectID'],
                'tagFilters' => [['App\User::1']],
            ])->andReturn([['objectID' => 'App\User::1']]);
            $usersIndexMock->shouldReceive('deleteObjects')->once()->with(['App\User::1']);

            $user->delete();
        } finally {
            AggregatorObserver::enableSyncingFor(Wall::class);
        }
    }

    public function testSkipForceDeletedWhenDisableSyncingForAggregator(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        $wallIndexMock = $this->mockIndex('wall');
        $usersIndexMock = $this->mockIndex('users');

        $usersIndexMock->shouldReceive('saveObjects')->once();

        $user = factory(User::class)->create();

        AggregatorObserver::disableSyncingFor(Wall::class);

        Wall::bootSearchable();

        try {
            // Ensure saveObjects is NOT called for the wall index (aggregator)
            $wallIndexMock->shouldNotReceive('browseObjects')->with([
                'attributesToRetrieve' => ['objectID'],
                'tagFilters' => [['App\User::1']],
            ])->andReturn([['objectID' => 'App\User::1']]);
            $wallIndexMock->shouldNotReceive('deleteObjects')->with(['App\User::1']);

            // Expect browseObjects and deleteObjects to be called on the users index
            $usersIndexMock->shouldReceive('browseObjects')->once()->with([
                'attributesToRetrieve' => ['objectID'],
                'tagFilters' => [['App\User::1']],
            ])->andReturn([['objectID' => 'App\User::1']]);
            $usersIndexMock->shouldReceive('deleteObjects')->once()->with(['App\User::1']);

            $user->forceDelete();
        } finally {
            AggregatorObserver::enableSyncingFor(Wall::class);
        }
    }

}

class DummyRemoveFromSearch {
    public function __construct($models)
    {
        if ($models->first() instanceof Aggregator) {
            throw new RuntimeException('Scout::$removeFromSearchJob dispatched with Aggregator');
        }
    }
    public function __invoke()
    {
    }
    public function onQueue()
    {
        return $this;
    }
    public function onConnection()
    {
        return $this;
    }
}
