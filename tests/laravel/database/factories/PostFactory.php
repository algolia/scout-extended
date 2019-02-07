<?php

declare(strict_types=1);

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'subject' => $faker->text,
    ];
});
