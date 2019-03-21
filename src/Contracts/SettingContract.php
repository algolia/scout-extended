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

namespace Algolia\ScoutExtended\Contracts;

/**
 * This class does things
 *
 * {@internal To access the company guidelines on modifying this class,
 * see {@link http://www.example.com Company Guidelines}, or contact
 * your supervisor}}}
 * Using this class can be very helpful for several reasons. etc. etc.
 * @internal the class uses the private methods {@link _foo()} and
 *  {@link _bar()} to do some wacky stuff
 */
interface SettingContract
{
    /**
     * Checks if the given key/value is a setting.
     *
     * @return array
     */
    public function getValue(string $key, $value, array $arrayValue): array;
}
