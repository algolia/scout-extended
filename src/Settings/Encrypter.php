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

/**
 * @internal
 */
final class Encrypter
{
    /**
     * @var \Algolia\ScoutExtended\Settings\RemoteRepository
     */
    private $remoteRepository;

    /**
     * Encrypter constructor.
     *
     * @param \Algolia\ScoutExtended\Settings\RemoteRepository $remoteRepository
     */
    public function __construct(RemoteRepository $remoteRepository)
    {
        $this->remoteRepository = $remoteRepository;
    }

    /**
     * Get the encrypted value from the path settings.
     *
     * @param  string $path
     *
     * @return string
     */
    public function fromPath(string $path): string
    {
        $settingsObject = new Settings(file_exists($path) ? require $path : [], $this->remoteRepository->defaults());

        return $this->local($settingsObject);
    }

    /**
     * Get the encrypted value from the local settings.
     *
     * @param  \Algolia\ScoutExtended\Settings\Settings $settings
     *
     * @return string
     */
    public function local(Settings $settings): string
    {
        return $this->encrypt($settings->compiled());
    }

    /**
     * Get the encrypted value from the remote settings.
     *
     * @param  \Algolia\ScoutExtended\Settings\Settings $settings
     *
     * @return string
     */
    public function remote(Settings $settings): string
    {
        return $this->encrypt($settings->compiled());
    }

    /**
     * Get the encrypted value from the given settings.
     *
     * @param  array $settings
     *
     * @return string
     */
    public function with(array $settings): string
    {
        $settingsObject = new Settings($settings, $this->remoteRepository->defaults());

        return $this->encrypt($settingsObject->compiled());
    }

    /**
     * @param  array $settings
     *
     * @return string
     */
    private function encrypt(array $settings): string
    {
        ksort($settings);

        ksort($settings['synonyms']);

        return md5(serialize($settings));
    }
}
