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
     * Get the encrypted value from the provided settings path.
     *
     * @param  string $path
     *
     * @return string
     */
    public function local(string $path): string
    {
        $settings = file_exists($path) ? require $path : [];

        return $this->with($settings);
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
        return $this->with($settings->compiled());
    }

    /**
     * Get the encrypted value from the array settings.
     *
     * @param  array $settings
     *
     * @return string
     */
    public function with(array $settings): string
    {
        return $this->encrypt($settings);
    }

    /**
     * @param  array $settings
     *
     * @return string
     */
    private function encrypt(array $settings): string
    {
        ksort($settings);

        return md5(serialize($settings));
    }
}
