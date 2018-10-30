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

namespace Algolia\ScoutExtended;

use function is_string;
use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\AnalyticsClient;
use Illuminate\Contracts\Container\Container;

final class Algolia
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $container;

    /**
     * Algolia constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get a index instance.
     *
     * @param  string|\Illuminate\Database\Eloquent\Model $model
     *
     * @return \Algolia\AlgoliaSearch\SearchIndex
     */
    public function index($model): SearchIndex
    {
        $model = is_string($model) ? new $model : $model;

        return $this->client()->initIndex($model->searchableAs());
    }

    /**
     * Get a client instance.
     *
     * @return \Algolia\AlgoliaSearch\SearchClient
     */
    public function client(): SearchClient
    {
        return $this->container->get('algolia.client');
    }

    /**
     * Get a analytics instance.
     *
     * @return \Algolia\AlgoliaSearch\AnalyticsClient
     */
    public function analytics(): AnalyticsClient
    {
        return $this->container->get('algolia.analytics');
    }
}
