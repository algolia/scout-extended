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

use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;

final class ClearCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:clear {model}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clear the index of the the given model';

    /**
     * {@inheritdoc}
     */
    public function handle(Algolia $algolia): void
    {
        $class = $this->argument('model');

        $algolia->index($class)->clear();

        $this->info('The ['.$class.'] index have been cleared.');
    }
}
