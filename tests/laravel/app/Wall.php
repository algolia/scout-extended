<?php

namespace App;

use Algolia\ScoutExtended\Search\Aggregator;

final class Wall extends Aggregator
{
    protected $models = [
        User::class,
        Thread::class,
        Post::class,
    ];
}
