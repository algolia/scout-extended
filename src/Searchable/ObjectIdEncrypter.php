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

use function get_class;

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
    public static function encrypt($searchable, int $part = null): string
    {
        $scoutKey = method_exists($searchable, 'getScoutKey') ? $searchable->getScoutKey() : $searchable->getKey();

        $meta = [get_class($searchable->getModel()), $scoutKey];

        if ($part !== null) {
            $meta[] = $part;
        }

        return implode(self::$separator, $meta);
    }

    /**
     * @param  string $objectId
     *
     * @return string
     */
    public static function withoutPart(string $objectId): string
    {
        return implode(self::$separator, [self::decryptSearchable($objectId), self::decryptSearchableKey($objectId)]);
    }

    /**
     * @param  string $objectId
     *
     * @return string
     */
    public static function decryptSearchable(string $objectId): string
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
