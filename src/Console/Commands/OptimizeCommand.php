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
use Illuminate\Support\Facades\File;
use Algolia\ScoutExtended\Settings\Compiler;
use Algolia\ScoutExtended\Settings\LocalFactory;
use Algolia\ScoutExtended\Settings\Synchronizer;
use Algolia\ScoutExtended\Helpers\SearchableFinder;
use Algolia\ScoutExtended\Settings\LocalRepository;

final class OptimizeCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:optimize
                            {model? : The name of the searchable model}';

    /**
     * {@inheritdoc}<<
     */
    protected $description = 'Optimize the given model creating a settings file';

    /**
     * {@inheritdoc}
     */
    public function handle(
        Algolia $algolia,
        Synchronizer $synchronizer,
        LocalFactory $localFactory,
        Compiler $compiler,
        SearchableFinder $searchableModelsFinder,
        LocalRepository $localRepository
    ): void {
        foreach ($searchableModelsFinder->fromCommand($this) as $searchable) {
            $this->output->text('🔎 Optimizing search experience in: <info>['.$searchable.']</info>');
            $status = $synchronizer->analyse($index = $algolia->index($searchable));
            if (! $localRepository->exists($index) || $this->confirm('Local settings already exists, do you wish to overwrite?')) {
                $settings = $localFactory->create($index, $searchable);
                $path = $localRepository->getPath($index);
                $compiler->compile($settings, $path);
                $this->output->success('Settings file created at: '.$path);
                $this->output->note('Please review the settings file and synchronize it with Algolia using: "'.ARTISAN_BINARY.' scout:sync"');
            }
        }
    }
}
