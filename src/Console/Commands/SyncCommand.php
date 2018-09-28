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
use Algolia\LaravelScoutExtended\Settings\StateResponse;
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
    protected $description = "Synchronize the local & remote settings of searchable models";

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
                case StateResponse::LOCAL_NOT_FOUND:
                    $io->comment('Local settings do not exist and remote settings found! Downloading remote settings...');
                    $synchronizer->download($index);
                    $io->success('Settings file created at: '.$state->getPath());
                    break;
                case StateResponse::REMOTE_NOT_FOUND:
                    $io->success('Remote settings does not exists. Uploading settings file: '.$state->getPath());
                    $synchronizer->upload($index);
                    break;
                case StateResponse::BOTH_ARE_EQUAL:
                    $io->success('Both local and remote settings are the equal.');
                    break;
                case StateResponse::LOCAL_GOT_UPDATED:
                    if ($io->confirm('You local settings is more recent than the remote one. Wish to upload the local settings?')) {
                        $io->comment('Uploading local settings...');
                        $synchronizer->upload($index);
                    }
                    break;
                case StateResponse::REMOTE_GOT_UPDATED:
                    if ($io->confirm('You remote configuration is more recent than the local one. Wish to download the remote settings?')) {
                        $io->comment('Downloading remote settings...');
                        $synchronizer->download($index);
                    }
                    break;
                case StateResponse::BOTH_GOT_UPDATED:
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
