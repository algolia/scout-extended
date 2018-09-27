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

namespace Algolia\LaravelScoutExtended\Console\Commands;

use Laravel\Scout\Searchable;
use Illuminate\Console\Command;
use Algolia\LaravelScoutExtended\Algolia;
use Algolia\LaravelScoutExtended\Settings\State;
use Symfony\Component\Console\Style\SymfonyStyle;
use Algolia\LaravelScoutExtended\Settings\Synchronizer;
use Algolia\LaravelScoutExtended\Helpers\SearchableModelsFinder;

final class SyncCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:sync
                            {model? : The name of the searchable model}
                            {--keep=none} : In conflict keep the given option';

    /**
     * {@inheritdoc}
     */
    protected $description = "Synchronize local & remote settings of searchable models";

    /**
     * {@inheritdoc}
     */
    public function handle(
        Algolia $algolia,
        Synchronizer $synchronizer,
        SearchableModelsFinder $searchableModelsFinder
    ): void {
        $classes = (array) $this->argument('model');

        $io = new SymfonyStyle($this->input, $this->output);

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $io->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');
        }

        foreach ($classes as $class) {
            $io->comment('Analysing ['.$class.'] index settings...');
            $state = $synchronizer->analyse($index = $algolia->index($class));

            switch ($state->toString()) {
                case State::LOCAL_NOT_FOUND:
                    $io->comment('No settins found locally! Downloading remote settings...');
                    $synchronizer->download($index);
                    $io->success('Settings file created at: '.$state->getPath());
                    break;
                case State::REMOTE_NOT_FOUND:
                    $io->success('No settings found remotely. Uploading settings file: '.$state->getPath());
                    $synchronizer->upload($index);
                    break;
                case State::BOTH_ARE_EQUAL:
                    $io->success('Both local and remote settings are up-to-date!');
                    break;
                case State::LOCAL_GOT_UPDATED:
                    if ($io->confirm('Remote settings are outdated. Wish to upload the local settings?')) {
                        $io->comment('Uploading local settings...');
                        $synchronizer->upload($index);
                    }
                    break;
                case State::REMOTE_GOT_UPDATED:
                    if ($io->confirm('Local settings are outdated. Wish to download the remote settings?')) {
                        $io->comment('Downloading remote settings...');
                        $synchronizer->download($index);
                    }
                    break;
                case State::BOTH_GOT_UPDATED:
                    $options = ['none', 'local', 'remote',];

                    $choice = $io->choice('Remote & Local settings got updated. Which one you want to preserve?', $options, $this->option('keep'));

                    switch ($choice) {
                        case 'local':
                            $io->comment('Uploading local settings...');
                            $synchronizer->upload($index);
                            break;
                        case 'remote':
                            $io->comment('Downloading remote settings...');
                            $synchronizer->download($index);
                            break;
                    }
                    break;
            }
        }
    }
}
