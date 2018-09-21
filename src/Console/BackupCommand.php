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

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Illuminate\Console\Command;
use Algolia\LaravelScoutExtended\Algolia;
use Algolia\LaravelScoutExtended\Contracts\Settings\SynchronizerContract;
use Symfony\Component\Console\Style\SymfonyStyle;

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
    public function handle(Algolia $algolia, SynchronizerContract $synchronizer)
    {
        $class = $this->argument('model');

        $io = new SymfonyStyle($this->input, $this->output);

        try {
            $synchronizer->backup($algolia->index($class));
        } catch (NotFoundException $e) {
            $io->error($e->getMessage());
            return 1;
        }

        $io->success('The ['.$class.'] index settings have been backed up.');
    }
}
