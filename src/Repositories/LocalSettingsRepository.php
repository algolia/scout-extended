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
use Illuminate\Filesystem\Filesystem;
use Algolia\AlgoliaSearch\SearchIndex;
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
     * @param  \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return bool
     */
    public function exists(SearchIndex $index): bool
    {
        return $this->files->exists($this->getPath($index));
    }

    /**
     * Get the settings path of the given index name.
     *
     * @param  \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return string
     */
    public function getPath(SearchIndex $index): string
    {
        $name = str_replace('_', '-', $index->getIndexName());

        $name = is_array($name) ? current($name) : $name;

        $fileName = 'scout-'.Str::lower($name).'.php';
        $settingsPath = config('scout.algolia.settings_path');

        if ($settingsPath === null) {
            return app('path.config').DIRECTORY_SEPARATOR.$fileName;
        }

        if (! $this->files->exists($settingsPath)) {
            $this->files->makeDirectory($settingsPath, 0755, true);
        }

        return $settingsPath.DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * Find the settings of the given Index.
     *
     * @param \Algolia\AlgoliaSearch\SearchIndex $index
     *
     * @return \Algolia\ScoutExtended\Settings\Settings
     */
    public function find(SearchIndex $index): Settings
    {
        return new Settings(($this->exists($index) ? require $this->getPath($index) : []),
            $this->remoteRepository->defaults());
    }
}
