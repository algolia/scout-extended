<?php

declare(strict_types=1);

namespace Tests\Features;

use Algolia\ScoutExtended\Facades\Algolia;
use App\User;
use App\Wall;
use Tests\TestCase;

final class SearchKeyTest extends TestCase
{
    public function testWhenSearchApiDontExists(): void
    {
        $this->mockClient()->shouldReceive('listApiKeys')->andReturn(['keys' => []]);

        $response = $this->mockResponse();
        $response->shouldReceive('getBody')->andReturn(['key' => 'bar']);

        $this->mockClient()->shouldReceive('addApiKey')->with(['search'], [
            'description' => config('app.name').'::searchKey',
        ])->andReturn($response);

        $this->mockClient()->shouldReceive('generateSecuredApiKey')->with('bar', [
            'restrictIndices' => 'users',
            'validUntil' => time() + (3600 * 25),
        ])->andReturn('barSecured');

        $this->assertSame(Algolia::searchKey(User::class), 'barSecured');
    }

    public function testWhenSearchApiDontExistsAndInvalidKeysExist(): void
    {
        $this->mockClient()->shouldReceive('listApiKeys')->andReturn(['keys' => [['foo' => 'bar']]]);

        $response = $this->mockResponse();
        $response->shouldReceive('getBody')->andReturn(['key' => 'bar']);

        $this->mockClient()->shouldReceive('addApiKey')->with(['search'], [
            'description' => config('app.name').'::searchKey',
        ])->andReturn($response);

        $this->mockClient()->shouldReceive('generateSecuredApiKey')->with('bar', [
            'restrictIndices' => 'users',
            'validUntil' => time() + (3600 * 25),
        ])->andReturn('barSecured');

        $this->assertSame(Algolia::searchKey(User::class), 'barSecured');
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
            'validUntil' => time() + (3600 * 25),
        ])->andReturn('barSecured');

        $this->assertSame(Algolia::searchKey(new Wall()), 'barSecured');
    }
}
