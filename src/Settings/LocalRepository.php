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
use Illuminate\Filesystem\Filesystem;

/**
 * @internal
 */
final class LocalRepository
{
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
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Checks if the given index settings exists.
     *
     * @param  string $index
     *
     * @return bool
     */
    public function exists(string $index): bool
    {
        return $this->files->exists($this->getPath($index));
    }

    /**
     * Get the settings path of the given index name.
     *
     * @param  string $index
     *
     * @return string
     */
    public function getPath(string $index): string
    {
        return config_path('scout-'.Str::lower($index).'.php');
    }
}
