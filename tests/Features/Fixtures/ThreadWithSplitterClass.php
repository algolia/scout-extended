<?php

namespace Tests\Features\Fixtures;

use App\Thread;
use Algolia\ScoutExtended\Splitters\HtmlSplitter;

class ThreadWithSplitterClass extends Thread
{
    protected $table = 'threads';

    public function splitBody($value): string
    {
        return HtmlSplitter::class;
    }
}
