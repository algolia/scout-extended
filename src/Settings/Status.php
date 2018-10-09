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

use LogicException;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * @internal
 */
final class Status
{
    /**
     * @var \Algolia\ScoutExtended\Settings\Encrypter
     */
    private $encrypter;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    /**
     * @var \Algolia\ScoutExtended\Settings\Settings
     */
    private $settings;

    /**
     * @var string
     */
    private $path;

    public const LOCAL_NOT_FOUND = 'localNotFound';

    public const  REMOTE_NOT_FOUND = 'remoteNotFound';

    public const  BOTH_ARE_EQUAL = 'bothAreEqual';

    public const  LOCAL_GOT_UPDATED = 'localGotUpdated';

    public const  REMOTE_GOT_UPDATED = 'remoteGotUpdated';

    public const  BOTH_GOT_UPDATED = 'bothGotUpdated';

    /**
     * Status constructor.
     *
     * @param \Algolia\ScoutExtended\Settings\Encrypter $encrypter
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Algolia\ScoutExtended\Settings\Settings $settings
     * @param string $path
     *
     * @return void
     */
    public function __construct(Encrypter $encrypter, Filesystem $files, Settings $settings, string $path)
    {
        $this->encrypter = $encrypter;
        $this->files = $files;
        $this->settings = $settings;
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function localNotFound(): bool
    {
        return ! $this->files->exists($this->path);
    }

    /**
     * @return bool
     */
    public function remoteNotFound(): bool
    {
        return empty($this->settings->previousHash());
    }

    /**
     * @return bool
     */
    public function bothAreEqual(): bool
    {
        return $this->encrypter->local($this->path) === $this->settings->previousHash() && $this->encrypter->remote($this->settings) === $this->settings->previousHash();
    }

    /**
     * @return bool
     */
    public function localGotUpdated(): bool
    {
        return $this->encrypter->local($this->path) !== $this->settings->previousHash() && $this->encrypter->remote($this->settings) === $this->settings->previousHash();
    }

    /**
     * @return bool
     */
    public function remoteGotUpdated(): bool
    {
        return $this->encrypter->local($this->path) === $this->settings->previousHash() && $this->encrypter->remote($this->settings) !== $this->settings->previousHash();
    }

    /**
     * @return bool
     */
    public function bothGotUpdated(): bool
    {
        return $this->encrypter->local($this->path) !== $this->settings->previousHash() && $this->encrypter->remote($this->settings) !== $this->settings->previousHash();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the current state.
     *
     * @return string
     */
    public function toString(): string
    {
        $methods = [
            self::LOCAL_NOT_FOUND,
            self::REMOTE_NOT_FOUND,
            self::BOTH_ARE_EQUAL,
            self::LOCAL_GOT_UPDATED,
            self::REMOTE_GOT_UPDATED,
            self::BOTH_GOT_UPDATED,
        ];

        foreach ($methods as $method) {
            if ($this->{$method}()) {
                return $method;
            }
        }

        throw new LogicException('This should not happen');
    }

    /**
     * Get a human description of the current status.
     *
     * @return string
     */
    public function toHumanString(): string
    {
        $string = Str::snake($this->toString());

        return Str::ucfirst(str_replace('_', ' ', $string));
    }
}
