<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\LaravelScoutExtended\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Algolia\AlgoliaSearch\Interfaces\IndexInterface index(string $model)
 * @method static \Algolia\AlgoliaSearch\Interfaces\ClientInterface client()
 * @method static \Algolia\AlgoliaSearch\Analytics analytics()
 *
 * @see \Algolia\LaravelScoutExtended\Algolia
 */
final class Algolia extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'algolia';
    }
}
