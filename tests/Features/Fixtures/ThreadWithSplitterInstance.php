<?php

namespace Tests\Features\Fixtures;

use Algolia\ScoutExtended\Splitters\HtmlSplitter;
use App\Thread;

class ThreadWithSplitterInstance extends Thread
{
    protected $table = 'threads';

    public function splitBody($value)
    {
        return HtmlSplitter::by('h1');
    }
}
