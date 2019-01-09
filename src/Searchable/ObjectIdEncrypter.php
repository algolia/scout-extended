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

use function count;
use function get_class;
use Algolia\ScoutExtended\Exceptions\ShouldReimportSearchableException;

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
     * @param int|null $part
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
        return (string) self::getSearchableExploded($objectId)[0];
    }

    /**
     * @param  string $objectId
     *
     * @return string
     */
    public static function decryptSearchableKey(string $objectId): string
    {
        return (string) self::getSearchableExploded($objectId)[1];
    }

    /**
     * @param  string $objectId
     *
     * @return string[]
     */
    private static function getSearchableExploded(string $objectId): array
    {
        $parts = explode(self::$separator, $objectId);

        if (! is_array($parts) || count($parts) < 2) {
            throw new ShouldReimportSearchableException('ObjectID seems invalid. You may need to
                re-import your data using the `scout-reimport` Artisan command.');
        }

        return $parts;
    }
}
