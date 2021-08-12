<?php

namespace Tests\Features\Fixtures;

trait ThreadSearchableTrait
{
    public function toSearchableArray()
    {
        return [
            'something' => 99,
        ];
    }
}
