<?php

namespace Tests\Features\Fixtures;

use App\Thread;
use Algolia\ScoutExtended\Transformers\ConvertDatesToTimestamps;

class ThreadWithSearchableArrayUsingDateTransform extends Thread
{
    protected  $table = 'threads';

    public function toSearchableArray(): array
    {
        $data = array_merge($this->toArray(), ['subscriber_count' => '100']);
        return $this->transform($data, [
            ConvertDatesToTimestamps::class,
        ]);
    }
}
