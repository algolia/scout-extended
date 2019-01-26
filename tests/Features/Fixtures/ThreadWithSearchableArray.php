<?php

namespace Tests\Features\Fixtures;

use App\Thread;

class ThreadWithSearchableArray extends Thread
{
    public function toSearchableArray(): array
    {
        return $this->toArray();
    }
}
