<?php

namespace Tests\Features\Fixtures;

use App\Thread;
use Algolia\ScoutExtended\Splitters\HtmlSplitter;

class ThreadWithSplitterInstance extends Thread
{
    protected $table = 'threads';

    public function splitBody($value)
    {
        return HtmlSplitter::by('h1');
    }
}
