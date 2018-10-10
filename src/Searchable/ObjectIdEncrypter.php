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

namespace Algolia\ScoutExtended\Searchable;

/**
 * @internal
 */
final class ObjectIdEncrypter
{
    /**
     * Holds the metadata separator.
     *
     * @var string
     */
    private static $separator = '::';

    /**
     * Encrypt the given searchable.
     *
     * @param  mixed $searchable
     *
     * @return string
     */
    public static function encrypt($searchable): string
    {
        $scoutKey = method_exists($searchable, 'getScoutKey') ? $searchable->getScoutKey() : $searchable->getKey();

        return implode(self::$separator, [
            UuidGenerator::getUuid($searchable->getModel()),
            $scoutKey,
        ]);
    }

    /**
     * @param  string $objectId
     *
     * @return string
     */
    public static function decryptSearchableUuid(string $objectId): string
    {
        return (string) explode(self::$separator, $objectId)[0];
    }

    /**
     * @param  string $objectId
     *
     * @return string
     */
    public static function decryptSearchableKey(string $objectId): string
    {
        return (string) explode(self::$separator, $objectId)[1];
    }
}
