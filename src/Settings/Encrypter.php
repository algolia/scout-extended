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

/**
 * @internal
 */
final class Encrypter
{
    /**
     * Encrypt the provided settings file.
     *
     * @param  string $path
     *
     * @return string
     */
    public function local(string $path): string
    {
        $settings = require $path;

        return $this->with($settings);
    }

    /**
     * @param  \Algolia\LaravelScoutExtended\Settings\Settings $settings
     *
     * @return string
     */
    public function remote(Settings $settings): string
    {
        return $this->with($settings->compiled());
    }

    /**
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
    private function encrypt(array $settings)
    {
        ksort($settings);

        return md5(serialize($settings));
    }
}
