<?php

declare(strict_types=1);

namespace Tests;

use ReflectionClass;
use Laravel\Scout\Engines\AlgoliaEngine;

final class AlgoliaSyncEngine extends AlgoliaEngine
{
    public function __construct(AlgoliaEngine $algoliaEngine)
    {
        $reflectionClass = new ReflectionClass(get_class($algoliaEngine));

        $reflectionProperty = $reflectionClass->getProperty('algolia');
        $reflectionProperty->setAccessible(true);
        $client = $reflectionProperty->getValue($algoliaEngine);
        $this->algolia = new SyncClient($client);
    }
}