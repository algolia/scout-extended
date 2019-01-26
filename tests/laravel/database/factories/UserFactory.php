<?php

declare(strict_types=1);

use App\Post;
use App\User;
use App\Thread;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'subject' => $faker->text,
    ];
});

$factory->define(Thread::class, function (Faker $faker) {
    return [
        'body' => $faker->text,
    ];
});

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});
