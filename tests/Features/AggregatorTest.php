<?php

declare(strict_types=1);

namespace Tests\Features;

use App\Post;
use App\User;
use App\Wall;
use App\Thread;
use Tests\TestCase;

final class AggregatorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(database_path('migrations'));
        $this->artisan('migrate:fresh', ['--database' => 'testbench'])->run();
    }

    public function testWhenAggregagorIsNotBooted(): void
    {
        $usersIndexMock = $this->mockIndex('users');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) && $argument[0]['objectID'] === 1;
        }));

        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('deleteObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && $argument[0] === 1;
        }));
        $user->delete();
    }

    public function testAggregatorWithSearchableModel(): void
    {
        Wall::bootSearchable();

        $usersIndexMock = $this->mockIndex('users');
        $wallIndexMock = $this->mockIndex('walls');

        $usersIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('email', $argument[0]) && $argument[0]['objectID'] === 1;
        }));
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {

            return count($argument) === 1 && array_key_exists('email', $argument[0]) && $argument[0]['objectID'] === 'users::1';
        }));
        $user = factory(User::class)->create();

        $usersIndexMock->shouldReceive('deleteObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && $argument[0] === 1;
        }));
        $wallIndexMock->shouldReceive('deleteObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && $argument[0] === 'users::1';
        }));
        $user->delete();
    }

    public function testAggregatorWithNonSearchableModel(): void
    {
        Wall::bootSearchable();

        $threadIndexMock = $this->mockIndex(Thread::class);
        $wallIndexMock = $this->mockIndex('walls');

        $threadIndexMock->shouldReceive('saveObjects')->once();
        $wallIndexMock->shouldReceive('saveObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('body', $argument[0]) && $argument[0]['objectID'] === 'threads::1';
        }));
        $thread = factory(Thread::class)->create();

        $threadIndexMock->shouldReceive('deleteObjects')->once();
        $wallIndexMock->shouldReceive('deleteObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && $argument[0] === 'threads::1';
        }));
        $thread->delete();
    }

    public function testAggregatorSoftDeleteModelWithoutSoftDeletesOnIndex(): void
    {
        Wall::bootSearchable();

        $wallIndexMock = $this->mockIndex('walls');

        // Laravel Scout restore calls twice the save objects.
        $wallIndexMock->shouldReceive('saveObjects')->times(3)->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) && $argument[0]['objectID'] === 'posts::1';
        }));
        // Laravel Scout force Delete calls twice the save objects.
        $wallIndexMock->shouldReceive('deleteObjects')->times(3)->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && $argument[0] === 'posts::1';
        }));
        $post = factory(Post::class)->create();
        $post->delete();
        $post->restore();
        $post->forceDelete();
    }

    public function testAggregatorSoftDeleteModelWithSoftDeletesOnIndex(): void
    {
        Wall::bootSearchable();

        $this->app['config']->set('scout.soft_delete', true);

        $wallIndexMock = $this->mockIndex('walls');

        // Laravel Scout force Delete calls once the save() method.
        $wallIndexMock->shouldReceive('saveObjects')->times(3)->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && array_key_exists('subject', $argument[0]) && $argument[0]['objectID'] === 'posts::1';
        }));
        $post = factory(Post::class)->create();
        $post->delete();

        $wallIndexMock->shouldReceive('deleteObjects')->once()->with(\Mockery::on(function ($argument) {
            return count($argument) === 1 && $argument[0] === 'posts::1';
        }));
        $post->forceDelete();
    }

    public function testAggregatorSearch(): void
    {
        Wall::bootSearchable();

        $threadIndexMock = $this->mockIndex(Thread::class);
        $wallIndexMock = $this->mockIndex('walls');

        $threadIndexMock->shouldReceive('saveObjects')->once();
        $wallIndexMock->shouldReceive('saveObjects')->twice();
        $wallIndexMock->shouldReceive('search')->once()->andReturn([
            'hits' => [
                [
                    'subject' => 'Sed neque est quos.',
                    'id' => 1,
                    'objectID' => 'posts::1',
                ],
                [
                    'body' => 'Saepe et delectus quis dolor sit unde voluptatibus. Quas blanditiis enim accusamus veniam.',
                    'id' => 1,
                    'objectID' => 'threads::1',
                ],
            ],
        ]);

        $post = factory(Post::class)->create();
        $thread = factory(Thread::class)->create();

        $models = Wall::search('input')->get();
        /* @var $models \Illuminate\Database\Eloquent\Collection */

        $this->assertCount(2, $models);
        $this->assertInstanceOf(Post::class, $models->get(0));
        $this->assertEquals($post->subject, $models->get(0)->subject);
        $this->assertEquals($post->id, $models->get(0)->id);

        $this->assertInstanceOf(Thread::class, $models->get(1));
        $this->assertEquals($thread->body, $models->get(1)->body);
        $this->assertEquals($thread->id, $models->get(1)->id);
    }
}
