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

namespace Algolia\LaravelScoutExtended\Settings;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Algolia\AlgoliaSearch\Interfaces\IndexInterface;
use Algolia\LaravelScoutExtended\Contracts\Settings\SynchronizerContract;

final class Synchronizer implements SynchronizerContract
{
    /**
     * @var \Algolia\LaravelScoutExtended\Settings\Compiler
     */
    private $compiler;

    /**
     * @var \Algolia\LaravelScoutExtended\Settings\DefaultSettingsDiscover
     */
    private $defaultSettingsDiscover;

    /**
     * Synchronizer constructor.
     *
     * @param \Algolia\LaravelScoutExtended\Settings\Compiler $compiler
     * @param \Algolia\LaravelScoutExtended\Settings\DefaultSettingsDiscover $defaultSettingsDiscover
     *
     * @return void
     */
    public function __construct(Compiler $compiler, DefaultSettingsDiscover $defaultSettingsDiscover)
    {
        $this->compiler = $compiler;
        $this->defaultSettingsDiscover = $defaultSettingsDiscover;
    }

    /**
     * {@inheritdoc}
     */
    public function backup(IndexInterface $index): void
    {
        $settings = new Settings($index->getSettings(), $this->defaultSettingsDiscover->getDefaults());

        $name = 'scout-'.Str::lower($index->getIndexName()).'.php';

        $this->compiler->compile($settings, config_path($name));
    }
}
