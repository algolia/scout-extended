<?php

declare(strict_types=1);

use Tests\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    public const COUNT = 3;

    public function run()
    {
        factory(User::class, self::COUNT)->create();
    }
}