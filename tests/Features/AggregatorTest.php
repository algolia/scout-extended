<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\Iterators\ObjectIterator;
use Algolia\ScoutExtended\Searchable\Aggregator;
use Algolia\ScoutExtended\Searchable\AggregatorCollection;
use Algolia\ScoutExtended\Searchable\Aggregators;
use App\All;
use App\News;
use App\Post;
use App\Thread;
use App\User;
use App\Wall;
use Laravel\Scout\Scout;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AggregatorTest extends TestCase
{
    public function testWhenAggregagorIsNotBooted(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        $usersIndexMock = $this->mockIndex('users');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));

        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\User::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\User::1'],
        ]);
        $usersIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\User::1',
        ]);

        $user->delete();
    }

    public function testWhenAggregagorIsNotBootedWithDeprecatedDeleteBy(): void
    {
        $usersIndexMock = $this->mockIndex('users');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));

        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\User::1'],
            ],
        ]);

        $user->delete();
    }

    public function testAggregatorWithSearchableModel(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Wall::bootSearchable();

        $usersIndexMock = $this->mockIndex('users');
        $wallIndexMock = $this->mockIndex('wall');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\User::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\User::1'],
        ]);
        $usersIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\User::1',
        ]);

        $wallIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\User::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\User::1'],
        ]);
        $wallIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\User::1',
        ]);

        $user->delete();
    }

    public function testAggregatorWithSearchableModelWithDeprecatedDeleteBy(): void
    {
        Wall::bootSearchable();

        $usersIndexMock = $this->mockIndex('users');
        $wallIndexMock = $this->mockIndex('wall');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\User::1'],
            ],
        ]);

        $wallIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\User::1'],
            ],
        ]);

        $user->delete();
    }

    public function testAggregatorWithNonSearchableModel(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Wall::bootSearchable();

        $threadIndexMock = $this->mockIndex(Thread::class);
        $wallIndexMock = $this->mockIndex('wall');

        $threadIndexMock->shouldReceive('saveObjects')->once();
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('body', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Thread::1';
        }));
        $thread = factory(Thread::class)->create();

        $threadIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\Thread::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\Thread::1'],
        ]);
        $threadIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\Thread::1',
        ]);

        $wallIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\Thread::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\Thread::1'],
        ]);
        $wallIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\Thread::1',
        ]);

        $thread->delete();
    }

    public function testAggregatorWithNonSearchableModelWithDeprecatedDeleteBy(): void
    {
        Wall::bootSearchable();

        $threadIndexMock = $this->mockIndex(Thread::class);
        $wallIndexMock = $this->mockIndex('wall');

        $threadIndexMock->shouldReceive('saveObjects')->once();
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('body', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Thread::1';
        }));
        $thread = factory(Thread::class)->create();

        $threadIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\Thread::1'],
            ],
        ]);

        $wallIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\Thread::1'],
            ],
        ]);
        $thread->delete();
    }

    public function testAggregatorSoftDeleteModelWithoutSoftDeletesOnIndex(): void
    {
        $this->app['config']->set('scout.algolia.use_deprecated_delete_by', false);

        Wall::bootSearchable();

        $wallIndexMock = $this->mockIndex('wall');

        // Laravel Scout restore calls twice the save objects.
        $wallIndexMock->shouldReceive('saveObjects')->times(3)->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $wallIndexMock->shouldReceive('browseObjects')->times(3)->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\Post::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\Post::1'],
        ]);
        $wallIndexMock->shouldReceive('deleteObjects')->times(3)->with([
            'App\Post::1',
        ]);
        $post = factory(Post::class)->create();
        $post->delete();
        $post->restore();
        $post->forceDelete();
    }

    public function testAggregatorSoftDeleteModelWithoutSoftDeletesOnIndexWithDeprecatedDeleteBy(): void
    {
        Wall::bootSearchable();

        $wallIndexMock = $this->mockIndex('wall');

        // Laravel Scout restore calls twice the save objects.
        $wallIndexMock->shouldReceive('saveObjects')->times(3)->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $wallIndexMock->shouldReceive('deleteBy')->times(3)->with([
            'tagFilters' => [
                ['App\Post::1'],
            ],
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

        $wallIndexMock = $this->mockIndex('wall');

        // Laravel Scout force Delete calls once the save() method.
        $wallIndexMock->shouldReceive('saveObjects')->times(3)->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $post = factory(Post::class)->create();
        $post->delete();

        $wallIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\Post::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\Post::1'],
        ]);
        $wallIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\Post::1',
        ]);

        $post->forceDelete();
    }

    public function testAggregatorSoftDeleteModelWithSoftDeletesOnIndexWithDeprecatedDeleteBy(): void
    {
        Wall::bootSearchable();

        $this->app['config']->set('scout.soft_delete', true);

        $wallIndexMock = $this->mockIndex('wall');

        // Laravel Scout force Delete calls once the save() method.
        $wallIndexMock->shouldReceive('saveObjects')->times(3)->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) &&
                $argument[0]['objectID'] === 'App\Post::1';
        }));
        $post = factory(Post::class)->create();
        $post->delete();

        $wallIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\Post::1'],
            ],
        ]);
        $post->forceDelete();
    }

    public function testAggregatorSearch(): void
    {
        Wall::bootSearchable();

        $threadIndexMock = $this->mockIndex(Thread::class);
        $wallIndexMock = $this->mockIndex('wall');

        $threadIndexMock->shouldReceive('saveObjects')->times(2);
        $wallIndexMock->shouldReceive('saveObjects')->times(4);
        $wallIndexMock->shouldReceive('search')->once()->andReturn([
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
        /* @var $models \Illuminate\Database\Eloquent\Collection */
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

        $usersIndexMock = $this->mockIndex('users');
        $wallIndexMock = $this->mockIndex('wall');
        $newsIndexMock = $this->mockIndex('news');

        $usersIndexMock->shouldReceive('saveObjects');
        $wallIndexMock->shouldReceive('saveObjects');
        $newsIndexMock->shouldReceive('saveObjects');

        $user = factory(User::class)->create();

        $response = ['hits' => [['objectID' => 'App\User::1']]];
        $wallIndexMock->shouldReceive('search')->once()->andReturn($response);
        $newsIndexMock->shouldReceive('search')->once()->andReturn($response);

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

        $usersIndexMock = $this->mockIndex('users');
        $wallIndexMock = $this->mockIndex('wall');
        $allIndexMock = $this->mockIndex('all');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $allIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\User::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\User::1'],
        ]);
        $usersIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\User::1',
        ]);

        $wallIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\User::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\User::1'],
        ]);
        $wallIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\User::1',
        ]);

        $allIndexMock->shouldReceive('browseObjects')->once()->with([
            'attributesToRetrieve' => [
                'objectID',
            ],
            'tagFilters' => [
                ['App\User::1'],
            ],
            // NOTE: This _should_ ideally return an instance of `\Algolia\AlgoliaSearch\Iterators\ObjectIterator`
            //       but mocking that class is not feasible as it has been declared `final`.
        ])->andReturn([
            ['objectID' => 'App\User::1'],
        ]);
        $allIndexMock->shouldReceive('deleteObjects')->once()->with([
            'App\User::1',
        ]);

        $user->delete();
    }

    public function testAggregatorWithMultipleBootsWithDeprecatedDeleteBy(): void
    {
        Aggregators::bootSearchables([
            Wall::class,
            All::class,
        ]);

        $usersIndexMock = $this->mockIndex('users');
        $wallIndexMock = $this->mockIndex('wall');
        $allIndexMock = $this->mockIndex('all');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $allIndexMock->shouldReceive('saveObjects')->once()->with(Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) &&
                $argument[0]['objectID'] === 'App\User::1';
        }));
        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\User::1'],
            ],
        ]);

        $wallIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\User::1'],
            ],
        ]);

        $allIndexMock->shouldReceive('deleteBy')->once()->with([
            'tagFilters' => [
                ['App\User::1'],
            ],
        ]);

        $user->delete();
    }

    public function testWhenAggregatorIsBootedBeforePlainScoutSearchableTrait(): void
    {
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
    }
}
