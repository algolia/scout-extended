<?php

namespace Tests\Features\Fixtures;

use App\Thread;

class ThreadWithSearchableArrayUsingTransform extends Thread
{
    public function toSearchableArray(): array
    {
        return $this->transform($this->toArray(), [
            ConvertToFoo::class,
        ]);
    }
}
