<?php

namespace App;

use Algolia\ScoutExtended\Searchable\Aggregator;

class News extends Aggregator
{
    protected $models = [
        User::class,
        Thread::class,
        Post::class,
    ];

    protected $relations = [
        User::class => ['threads'],
    ];
}
