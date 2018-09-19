<?php

declare(strict_types=1);

namespace Tests;

use Algolia\AlgoliaSearch\Index;

final class SyncIndex
{
    private $realIndex;

    public function __construct(Index $realIndex)
    {
        $this->realIndex = $realIndex;
    }

    public function __call($name, $arguments)
    {
        $response = call_user_func_array(array($this->realIndex, $name), $arguments);

        if (is_array($response) && isset($response['taskID'])) {
            $this->realIndex->waitTask($response['taskID']);
        }

        return $response;
    }
}
