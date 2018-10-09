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

namespace Algolia\ScoutExtended\Console\Commands;

use Illuminate\Console\GeneratorCommand;

final class MakeAggregatorCommand extends GeneratorCommand
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:make-aggregator {name : The name of the aggregator}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a new aggregator class';

    /**
     * {@inheritdoc}
     */
    protected $type = 'Aggregator class';

    /**
     * {@inheritdoc}
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/aggregator.stub';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Search';
    }
}
