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
use Symfony\Component\Console\Style\SymfonyStyle;
use Algolia\ScoutExtended\Helpers\SearchableModelsFinder;

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
        SearchableModelsFinder $searchableModelsFinder
    ) {
        $classes = (array) $this->argument('model');

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $this->output->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');

            return 1;
        }

        foreach ($classes as $class) {
            $this->output->text('🔎 Optimizing search experience in: <info>['.$class.']</info>');
            $state = $synchronizer->analyse($algolia->index($class));
            if (! File::exists($state->getPath()) || $this->confirm('Local settings already exists, do you wish to overwrite?')) {
                $settings = $localFactory->create($class);
                $compiler->compile($settings, $state->getPath());
                $this->output->success('Settings file created at: '.$state->getPath());
                $this->output->note('Please review the settings file and synchronize it with Algolia using: "'.ARTISAN_BINARY.' scout:sync"');
            }
        }
    }
}
