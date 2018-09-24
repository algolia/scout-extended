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

use function in_array;

/**
 * @internal
 */
final class Settings
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var string[]
     */
    private static $ignore = [
        'version',
        'userData',
    ];

    /**
     * The default options.
     *
     * @var array
     */
    private $defaults;

    /**
     * Settings constructor.
     *
     * @param array $settings
     * @param array $defaults
     *
     * @return void
     */
    public function __construct(array $settings, array $defaults)
    {
        $this->settings = $settings;
        $this->defaults = $defaults;
    }

    /**
     * Get all of the items in the settings.
     *
     * @return array
     */
    public function all(): array
    {
        return array_filter($this->settings, function ($value, $setting) {
            return ! in_array($setting, self::$ignore, true);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get the changed items in the settings.
     *
     * @return array
     */
    public function changed(): array
    {
        return array_filter($this->all(), function ($value, $setting) {
            return ! array_key_exists($setting, $this->defaults) || $value !== $this->defaults[$setting];
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get the changed items in the settings.
     *
     * @return array
     */
    public function compiled(): array
    {
        $viewVariables = Compiler::getViewVariables();
        $changed = $this->changed();

        return array_filter($this->all(), function ($value, $setting) use ($viewVariables, $changed) {
            return in_array($setting, $viewVariables, true) || array_key_exists($setting, $changed);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get the hash.
     *
     * @return string
     */
    public function previousHash(): string
    {
        return $this->settings['userData'] ?? '';
    }
}
