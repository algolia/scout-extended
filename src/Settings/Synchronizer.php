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

namespace Algolia\ScoutExtended\Settings;

use Algolia\AlgoliaSearch\Index;
use Illuminate\Filesystem\Filesystem;
use Algolia\ScoutExtended\Repositories\LocalSettingsRepository;
use Algolia\ScoutExtended\Repositories\RemoteSettingsRepository;

/**
 * @internal
 */
class Synchronizer
{
    /**
     * @var \Algolia\ScoutExtended\Settings\Compiler
     */
    private $compiler;

    /**
     * @var \Algolia\ScoutExtended\Settings\Encrypter
     */
    private $encrypter;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    /**
     * @var \Algolia\ScoutExtended\Repositories\LocalSettingsRepository
     */
    private $localRepository;

    /**
     * @var \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository
     */
    private $remoteRepository;

    /**
     * Synchronizer constructor.
     *
     * @param \Algolia\ScoutExtended\Settings\Compiler $compiler
     * @param \Algolia\ScoutExtended\Settings\Encrypter $encrypter
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Algolia\ScoutExtended\Repositories\LocalSettingsRepository $localRepository
     * @param \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository $remoteRepository
     *
     * @return void
     */
    public function __construct(
        Compiler $compiler,
        Encrypter $encrypter,
        Filesystem $files,
        LocalSettingsRepository $localRepository,
        RemoteSettingsRepository $remoteRepository
    ) {
        $this->compiler = $compiler;
        $this->encrypter = $encrypter;
        $this->localRepository = $localRepository;
        $this->remoteRepository = $remoteRepository;
        $this->files = $files;
    }

    /**
     * Analyses the settings of the given index.
     *
     * @param \Algolia\AlgoliaSearch\Index $index
     *
     * @return \Algolia\ScoutExtended\Settings\Status
     */
    public function analyse(Index $index): Status
    {
        $remoteSettings = $this->remoteRepository->find($index);

        return new Status($this->localRepository, $this->encrypter, $remoteSettings, $index);
    }

    /**
     * Downloads the settings of the given index.
     *
     * @param \Algolia\AlgoliaSearch\Index $index
     *
     * @return void
     */
    public function download(Index $index): void
    {
        $settings = $this->remoteRepository->find($index);

        $path = $this->localRepository->getPath($index);

        $this->compiler->compile($settings, $path);

        $userData = $this->encrypter->encrypt($settings);

        $index->setSettings(['userData' => $userData])->wait();
    }

    /**
     * Uploads the settings of the given index.
     *
     * @param \Algolia\AlgoliaSearch\Index $index
     *
     * @return void
     */
    public function upload(Index $index): void
    {
        $settings = $this->localRepository->find($index);

        $userData = $this->encrypter->encrypt($settings);

        $index->setSettings(array_merge($settings->compiled(), ['userData' => $userData]))->wait();
    }
}
