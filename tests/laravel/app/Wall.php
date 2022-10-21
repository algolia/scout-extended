<?php

namespace App;

use Algolia\ScoutExtended\Searchable\Aggregator;

class Wall extends Aggregator
{
    protected $models = [
        User::class,
        Thread::class,
        Post::class,
    ];
}
