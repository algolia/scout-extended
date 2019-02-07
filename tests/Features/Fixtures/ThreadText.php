<?php

namespace Tests\Features\Fixtures;

use App\Thread;

class ThreadText extends Thread
{
    protected $table = 'threads';

    public function splitBody($value): array
    {
        return explode(',', $value);
    }
}
