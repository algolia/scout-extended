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
use Algolia\ScoutExtended\Exceptions\SettingsNotFound;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * @internal
 */
final class LocalRepository
{
    /**
     * @var \Algolia\ScoutExtended\Settings\RemoteRepository
     */
    private $remoteRepository;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    /**
     * LocalRepository constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     *
     * @return void
     */
    public function __construct(RemoteRepository $remoteRepository, Filesystem $files)
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
        return new Settings(($this->exists($index) ? require $this->getPath($index) : []), $this->remoteRepository->defaults());
    }
}
