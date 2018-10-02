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

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $this->output->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');

            return 1;
        }

        foreach ($classes as $class) {
            $this->output->text('🔎 Analysing information from: <info>['.$class.']</info>');
            $state = $synchronizer->analyse($index = $algolia->index($class));

            switch ($state->toString()) {
                case StateResponse::LOCAL_NOT_FOUND:
                    if ($state->remoteNotFound()) {
                        $this->output->note('No settings found.');
                        if ($this->output->confirm('Wish to optimize the search experience based on information from your model class?')) {
                            return $this->call('scout:optimize', ['model' => $class]);
                        }
                    } else {
                        $this->output->note('Remote settings <info>found</info>!');
                        $this->output->newLine();
                    }

                    $this->output->text('⬇️  Downloading <info>remote</info> settings...');
                    $synchronizer->download($index);
                    $this->output->success('Settings file created at: '.$state->getPath());
                    break;
                case StateResponse::REMOTE_NOT_FOUND:
                    $this->output->success('Remote settings does not exists. Uploading settings file: '.$state->getPath());
                    $synchronizer->upload($index);
                    break;
                case StateResponse::BOTH_ARE_EQUAL:
                    $this->output->success('Local and remote settings are similar.');
                    break;
                case StateResponse::LOCAL_GOT_UPDATED:
                    if ($this->output->confirm('Local settings got updated. Wish to upload them?')) {
                        $this->output->text('Uploading <info>local settings</info>...');
                        $this->output->newLine();
                        $synchronizer->upload($index);
                    }
                    break;
                case StateResponse::REMOTE_GOT_UPDATED:
                    if ($this->output->confirm('Remote settings got updated. Wish to download them?')) {
                        $this->output->text('Downloading <info>remote settings</info>...');
                        $this->output->newLine();
                        $synchronizer->download($index);
                    }
                    break;
                case StateResponse::BOTH_GOT_UPDATED:
                    $options = ['none', 'local', 'remote'];

                    $choice = $this->output->choice('Remote & Local settings got updated. Which one you want to preserve?', $options, $this->option('keep'));

                    switch ($choice) {
                        case 'local':
                            $this->output->text('Uploading <info>local settings</info>...');
                            $this->output->newLine();
                            $synchronizer->upload($index);
                            break;
                        case 'remote':
                            $this->output->text('Downloading <info>remote settings</info>...');
                            $this->output->newLine();
                            $synchronizer->download($index);
                            break;
                    }
                    break;
            }
        }

        $this->output->text('💡 Feedback: <info>https://github.com/algolia/scout-extended</info>');
        $this->output->newLine();
    }
}
