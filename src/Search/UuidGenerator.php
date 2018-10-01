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

namespace Algolia\ScoutExtended\Search;

use function get_class;
use function is_object;
use Illuminate\Support\Str;

/**
 * @internal
 */
final class UuidGenerator
{
    /**
     * Returns an uuid of the provided object/class.
     *
     * @param  string|object $class
     *
     * @return string
     */
    public static function getUuid($class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return str_replace('\\', '', Str::snake(Str::plural(class_basename($class))));
    }

    /**
     * KeyBy the given array of classes by their Uuid
     *
     * @param  array $classes
     *
     * @return array
     */
    public static function keyByUuid(array $classes): array
    {
        $result = [];
        foreach ($classes as $class) {
            $result[self::getUuid($class)] = $class;
        }

        return $result;
    }
}
