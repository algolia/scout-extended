<?php

declare(strict_types=1);

use App\EmptyItem;
use Faker\Generator as Faker;

$factory->define(EmptyItem::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
    ];
});
