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
use Algolia\AlgoliaSearch\Config\SearchConfig;

class EngineManager extends BaseEngineManager
{
    /**
     * Create an Algolia engine instance.
     *
     * @return \Algolia\ScoutExtended\Engines\AlgoliaEngine
     */
    public function createAlgoliaDriver(): AlgoliaEngine
    {
        UserAgent::addCustomUserAgent('Laravel Scout Extended', '3.2.2');

        $config = SearchConfig::create(
            config('scout.algolia.id'),
            config('scout.algolia.secret')
        )->setDefaultHeaders(
            $this->defaultAlgoliaHeaders()
        );

        if (is_int($connectTimeout = config('scout.algolia.connect_timeout'))) {
            $config->setConnectTimeout($connectTimeout);
        }

        if (is_int($readTimeout = config('scout.algolia.read_timeout'))) {
            $config->setReadTimeout($readTimeout);
        }

        if (is_int($writeTimeout = config('scout.algolia.write_timeout'))) {
            $config->setWriteTimeout($writeTimeout);
        }

        if (is_int($batchSize = config('scout.algolia.batch_size'))) {
            $config->setBatchSize($batchSize);
        }

        return new AlgoliaEngine(SearchClient::createWithConfig($config));
    }

    /**
     * Set the default Algolia configuration headers.
     *
     * @return array
     */
    protected function defaultAlgoliaHeaders()
    {
        if (! config('scout.identify')) {
            return [];
        }

        $headers = [];

        if (! config('app.debug') &&
            filter_var($ip = request()->ip(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
        ) {
            $headers['X-Forwarded-For'] = $ip;
        }

        if (($user = request()->user()) && method_exists($user, 'getKey')) {
            $headers['X-Algolia-UserToken'] = $user->getKey();
        }

        return $headers;
    }
}
