<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Tests\Features\Fixtures\ThreadWithSplitterClass;

$factory->define(ThreadWithSplitterClass::class, function (Faker $faker) {
    $content = '<h1>Hello Foo!</h1><h2>Hello Bar!</h2>';
    return [
        'user_id' => 1,
        'body' => $content,
    ];
});
