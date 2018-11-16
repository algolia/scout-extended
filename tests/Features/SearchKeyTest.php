<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use App\Wall;
use Tests\TestCase;
use Algolia\ScoutExtended\Facades\Algolia;

final class SearchKeyTest extends TestCase
{
    public function testWhenSearchApiDontExists(): void
    {
        $this->mockClient()->shouldReceive('listApiKeys')->andReturn(['keys' => []]);

        $response = $this->mockResponse();
        $response->shouldReceive('getBody')->andReturn(['key' => 'bar']);

        $this->mockClient()->shouldReceive('addApiKey')->with([
            'acl' => ['search'],
            'description' => config('app.name').'::searchKey',
        ])->andReturn($response);

        $this->mockClient()->shouldReceive('generateSecuredApiKey')->with('bar', [
            'restrictIndices' => 'users',
        ])->andReturn('barSecured');

        $this->assertEquals(Algolia::searchKey(User::class), 'barSecured');
    }

    public function testWhenSearchApiAlreadyExists(): void
    {
        $this->mockClient()->shouldReceive('listApiKeys')->andReturn(['keys' => [
            [
                'description' => config('app.name').'::searchKey',
                'value' => 'bar',
            ],
        ]]);

        $this->mockClient()->shouldReceive('generateSecuredApiKey')->with('bar', [
            'restrictIndices' => 'wall',
        ])->andReturn('barSecured');

        $this->assertEquals(Algolia::searchKey(new Wall()), 'barSecured');
    }
}
