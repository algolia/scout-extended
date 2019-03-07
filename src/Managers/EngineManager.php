<?php

declare(strict_types=1);

/**
 * This file is part of Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\ScoutExtended\Managers;

use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Support\UserAgent;
use Algolia\ScoutExtended\Engines\AlgoliaEngine;
use Laravel\Scout\EngineManager as BaseEngineManager;

class EngineManager extends BaseEngineManager
{
    /**
     * Create an Algolia engine instance.
     *
     * @return \Algolia\ScoutExtended\Engines\AlgoliaEngine
     */
    public function createAlgoliaDriver(): AlgoliaEngine
    {
        UserAgent::addCustomUserAgent('Laravel Scout Extended', '1.6.0');

        return new AlgoliaEngine(SearchClient::create(config('scout.algolia.id'), config('scout.algolia.secret')));
    }
}
