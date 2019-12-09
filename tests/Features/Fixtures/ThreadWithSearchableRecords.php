<?php

namespace Tests\Features\Fixtures;

use App\Thread;

class ThreadWithSearchableRecords extends Thread
{
    protected $table = 'threads';

    public function toSearchableRecords(): array
    {
        return [
            array_merge($this->toArray(), [ '_i' => 2 ]),
            array_merge($this->toArray(), [ '_i' => 4 ]),
            array_merge($this->toArray(), [ '_i' => 8 ]),
        ];
    }
}
