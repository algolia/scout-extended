<?php

declare(strict_types=1);

namespace Tests\Features;

use App\User;
use Tests\TestCase;

final class UnsearchableTest extends TestCase
{
    public function testUnsearchable(): void
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
