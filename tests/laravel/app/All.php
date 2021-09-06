<?php

namespace App;

use Algolia\ScoutExtended\Searchable\Aggregator;

final class All extends Aggregator
{
    protected $models = [
        User::class,
        Thread::class,
        Post::class,
    ];
}
