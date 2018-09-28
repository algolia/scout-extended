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
use Illuminate\Support\Facades\File;
use Algolia\LaravelScoutExtended\Algolia;
use Symfony\Component\Console\Style\SymfonyStyle;
use Algolia\LaravelScoutExtended\Settings\Compiler;
use Algolia\LaravelScoutExtended\Settings\Synchronizer;
use Algolia\LaravelScoutExtended\Settings\LocalFactory;
use Algolia\LaravelScoutExtended\Helpers\SearchableModelsFinder;

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
    protected $description = "Optimize local settings of searchable models";

    /**
     * {@inheritdoc}
     */
    public function handle(
        Algolia $algolia,
        Synchronizer $synchronizer,
        LocalFactory $localFactory,
        Compiler $compiler,
        SearchableModelsFinder $searchableModelsFinder
    ): void {
        $classes = (array) $this->argument('model');

        $io = new SymfonyStyle($this->input, $this->output);

        if (empty($classes) && empty($classes = $searchableModelsFinder->find())) {
            $io->error('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');
        }

        foreach ($classes as $class) {
            $state = $synchronizer->analyse($algolia->index($class));
            if (! File::exists($state->getPath()) || $this->confirm('File already exists, do you wish to overwrite?')) {
                $io->comment('Reading information from ['.$class.'] model...');
                $settings = $localFactory->create($class);
                $compiler->compile($settings, $state->getPath());
                $io->success('Settings file created at: '.$state->getPath());
            }
        }
    }
}
