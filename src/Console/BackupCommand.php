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

namespace Algolia\LaravelScoutExtended\Console;

use Illuminate\Console\Command;
use Algolia\LaravelScoutExtended\Algolia;
use Algolia\LaravelScoutExtended\Contracts\Settings\SynchronizerContract;

final class BackupCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:backup-settings {model}';

    /**
     * {@inheritdoc}
     */
    protected $description = "Back up the settings of the given model";

    /**
     * {@inheritdoc}
     */
    public function handle(Algolia $algolia, SynchronizerContract $synchronizer): void
    {
        $class = $this->argument('model');

        $synchronizer->backup($algolia->index($class));

        $this->info('The ['.$class.'] index settings have been backed up.');
    }
}
