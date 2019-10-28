<?php

namespace Tests\Features\Fixtures;

use Algolia\ScoutExtended\Searchable\Aggregator;

class ThreadAggregatorUsingOnlyDateTransform extends Aggregator
{
    protected $models = [
        ThreadWithSearchableArrayUsingDateTransform::class,
    ];
}
