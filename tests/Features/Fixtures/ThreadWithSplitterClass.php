<?php

namespace Tests\Features\Fixtures;

use Algolia\ScoutExtended\Splitters\HtmlSplitter;
use App\Thread;

class ThreadWithSplitterClass extends Thread
{
    protected $table = 'threads';

    public function splitBody($value): string
    {
        return HtmlSplitter::class;
    }
}
