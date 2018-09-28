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

use Laravel\Scout\Searchable;
use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;
use Symfony\Component\Console\Style\SymfonyStyle;
use Algolia\ScoutExtended\Settings\Synchronizer;
use Algolia\ScoutExtended\Settings\StateResponse;
use Algolia\ScoutExtended\Helpers\SearchableModelsFinder;

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
    protected $description = 'Synchronize the given model settings';

    /**
     * {@inheritdoc}
     */
    public function handle(
        Algolia $algolia,
        Synchronizer $synchronizer,
        SearchableModelsFinder $searchableModelsFinder
    ) {
        $classes = (array) $this->argument('model');

        $io = new SymfonyStyle($this->input, $this->output);

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $io->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');

            return 1;
        }

        foreach ($classes as $class) {
            $io->text('🔎 Analysing information from: <info>['.$class.']</info>');
            $state = $synchronizer->analyse($index = $algolia->index($class));

            switch ($state->toString()) {
                case StateResponse::LOCAL_NOT_FOUND:
                    if ($state->remoteNotFound()) {
                        $io->note('No settings found.');
                        if ($this->confirm('Wish to optimize the search experience based on information from your model class?')) {
                            return $this->call('scout:optimize', ['model' => $class]);
                        }
                    } else {
                        $io->note('Remote settings <info>found</info>!');
                        $io->newLine();
                    }

                    $io->text('⬇️  Downloading <info>remote</info> settings...');
                    $synchronizer->download($index);
                    $io->success('Settings file created at: '.$state->getPath());
                    break;
                case StateResponse::REMOTE_NOT_FOUND:
                    $io->success('Remote settings does not exists. Uploading settings file: '.$state->getPath());
                    $synchronizer->upload($index);
                    break;
                case StateResponse::BOTH_ARE_EQUAL:
                    $io->success('Local and remote settings are similar.');
                    break;
                case StateResponse::LOCAL_GOT_UPDATED:
                    if ($io->confirm('Local settings got updated. Wish to upload them?')) {
                        $io->text('Uploading <info>local settings</info>...');
                        $io->newLine();
                        $synchronizer->upload($index);
                    }
                    break;
                case StateResponse::REMOTE_GOT_UPDATED:
                    if ($io->confirm('Remote settings got updated. Wish to download them?')) {
                        $io->text('Downloading <info>remote settings</info>...');
                        $io->newLine();
                        $synchronizer->download($index);
                    }
                    break;
                case StateResponse::BOTH_GOT_UPDATED:
                    $options = ['none', 'local', 'remote'];

                    $choice = $io->choice('Remote & Local settings got updated. Which one you want to preserve?', $options, $this->option('keep'));

                    switch ($choice) {
                        case 'local':
                            $io->text('Uploading <info>local settings</info>...');
                            $io->newLine();
                            $synchronizer->upload($index);
                            break;
                        case 'remote':
                            $io->text('Downloading <info>remote settings</info>...');
                            $io->newLine();
                            $synchronizer->download($index);
                            break;
                    }
                    break;
            }
        }

        $io->text('💡 Feedback: <info>https://github.com/algolia/scout-extended</info>');
        $io->newLine();
    }
}
