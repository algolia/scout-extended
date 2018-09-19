<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Scout\EngineManager;

final class EngineManagerDouble extends EngineManager
{
    public function driver($driver = null)
    {
        $driver = parent::driver($driver);

        return new AlgoliaSyncEngine($driver);
    }
}