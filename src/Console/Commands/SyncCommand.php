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

use Algolia\ScoutExtended\Settings\LocalRepository;
use Laravel\Scout\Searchable;
use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;
use Algolia\ScoutExtended\Settings\Status;
use Algolia\ScoutExtended\Settings\Synchronizer;
use Algolia\ScoutExtended\Helpers\SearchableFinder;

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
        SearchableFinder $searchableModelsFinder,
        LocalRepository $localRepository
    ) {
        foreach ($searchableModelsFinder->fromCommand($this) as $searchable) {
            $this->output->text('🔎 Analysing settings from: <info>['.$searchable.']</info>');
            $status = $synchronizer->analyse($index = $algolia->index($searchable));
            $path = $localRepository->getPath($index);

            switch ($status->toString()) {
                case Status::LOCAL_NOT_FOUND:
                    if ($status->remoteNotFound()) {
                        $this->output->note('No settings found.');
                        if ($this->output->confirm('Wish to optimize the search experience based on information from your model class?')) {
                            return $this->call('scout:optimize', ['model' => $searchable]);
                        }
                    } else {
                        $this->output->note('Remote settings <info>found</info>!');
                        $this->output->newLine();
                    }

                    $this->output->text('⬇️  Downloading <info>remote</info> settings...');
                    $synchronizer->download($index);
                    $this->output->success('Settings file created at: '.$path);
                    break;
                case Status::REMOTE_NOT_FOUND:
                    $this->output->success('Remote settings does not exists. Uploading settings file: '.$path);
                    $synchronizer->upload($index);
                    break;
                case Status::BOTH_ARE_EQUAL:
                    $this->output->success('Local and remote settings are similar.');
                    break;
                case Status::LOCAL_GOT_UPDATED:
                    if ($this->output->confirm('Local settings got updated. Wish to upload them?')) {
                        $this->output->text('Uploading <info>local settings</info>...');
                        $this->output->newLine();
                        $synchronizer->upload($index);
                    }
                    break;
                case Status::REMOTE_GOT_UPDATED:
                    if ($this->output->confirm('Remote settings got updated. Wish to download them?')) {
                        $this->output->text('Downloading <info>remote settings</info>...');
                        $this->output->newLine();
                        $synchronizer->download($index);
                    }
                    break;
                case Status::BOTH_GOT_UPDATED:
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

        $this->output->newLine();
    }
}
