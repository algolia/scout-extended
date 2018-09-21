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
use Algolia\AlgoliaSearch\Interfaces\IndexInterface;
use Algolia\LaravelScoutExtended\Contracts\Settings\SynchronizerContract;

final class Synchronizer implements SynchronizerContract
{
    /**
     * @var \Algolia\LaravelScoutExtended\Settings\Compiler
     */
    private $compiler;

    /**
     * @var \Algolia\LaravelScoutExtended\Settings\SettingsDiscover
     */
    private $settingsDiscover;

    /**
     * Synchronizer constructor.
     *
     * @param \Algolia\LaravelScoutExtended\Settings\Compiler $compiler
     * @param \Algolia\LaravelScoutExtended\Settings\SettingsDiscover $settingsDiscover
     *
     * @return void
     */
    public function __construct(Compiler $compiler, SettingsDiscover $settingsDiscover)
    {
        $this->compiler = $compiler;
        $this->settingsDiscover = $settingsDiscover;
    }

    /**
     * {@inheritdoc}
     */
    public function backup(IndexInterface $index): void
    {
        $settings = new Settings($this->settingsDiscover->from($index), $this->settingsDiscover->defaults());

        $name = 'scout-'.Str::lower($index->getIndexName()).'.php';

        $this->compiler->compile($settings, config_path($name));
    }
}
