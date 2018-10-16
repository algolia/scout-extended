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

namespace Algolia\ScoutExtended\Repositories;

use Illuminate\Support\Str;
use Algolia\AlgoliaSearch\Index;
use Illuminate\Filesystem\Filesystem;
use Algolia\ScoutExtended\Settings\Settings;

/**
 * @internal
 */
final class LocalSettingsRepository
{
    /**
     * @var \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository
     */
    private $remoteRepository;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    /**
     * LocalRepository constructor.
     *
     * @param \Algolia\ScoutExtended\Repositories\RemoteSettingsRepository $remoteRepository
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(RemoteSettingsRepository $remoteRepository, Filesystem $files)
    {
        $this->remoteRepository = $remoteRepository;
        $this->files = $files;
    }

    /**
     * Checks if the given index settings exists.
     *
     * @param  \Algolia\AlgoliaSearch\Index $index
     *
     * @return bool
     */
    public function exists(Index $index): bool
    {
        return $this->files->exists($this->getPath($index));
    }

    /**
     * Get the settings path of the given index name.
     *
     * @param  \Algolia\AlgoliaSearch\Index $index
     *
     * @return string
     */
    public function getPath(Index $index): string
    {
        $name = str_replace('_', '-', $index->getIndexName());

        $name = is_array($name) ? current($name) : $name;

        return config_path('scout-'.Str::lower($name).'.php');
    }

    /**
     * Find the settings of the given Index.
     *
     * @param \Algolia\AlgoliaSearch\Index $index
     *
     * @return \Algolia\ScoutExtended\Settings\Settings
     */
    public function find(Index $index): Settings
    {
        return new Settings(($this->exists($index) ? require $this->getPath($index) : []),
            $this->remoteRepository->defaults());
    }
}
