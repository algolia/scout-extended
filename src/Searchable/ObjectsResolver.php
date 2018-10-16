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
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

/**
 * @internal
 */
final class ObjectsResolver
{
    /**
     * Contains a list of splittables searchables.
     *
     * @var [
     *      '\App\Thread' => true,
     *      '\App\User' => false,
     * ];
     */
    private $splittables = [];

    /**
     * Get an collection of objects to update
     * from the given searchables.
     *
     * @param \Illuminate\Support\Collection $searchables
     *
     * @return \Illuminate\Support\Collection
     */
    public function toUpdate(Collection $searchables): Collection
    {
        $result = [];

        foreach ($searchables as $key => $searchable) {

            if (empty($array = array_merge($searchable->toSearchableArray(), $searchable->scoutMetadata()))) {
                continue;
            }

            if ($this->shouldBeSplitted($searchable)) {
                [$pieces, $splittedBy] = $this->splitSearchable($searchable, $array);
                foreach ($pieces as $number => $piece) {
                    $array['objectID'] = ObjectIdEncrypter::encrypt($searchable, $number);
                    $array[$splittedBy] = $piece;
                    $result[] = $array;
                }
            } else {
                $array['objectID'] = ObjectIdEncrypter::encrypt($searchable);
                $result[] = $array;
            }
        }

        return collect($result);
    }

    /**
     * @param  object $searchable
     *
     * @return bool
     */
    private function shouldBeSplitted($searchable): bool
    {
        $class = get_class($searchable->getModel());

        if (array_key_exists($class, $this->splittables)) {
            return $this->splittables[$class];
        }

        $this->splittables[$class] = false;

        foreach ($searchable->toSearchableArray() as $key => $value) {
            $method = 'split'.Str::camel($key);
            $model = $searchable->getModel();
            if (method_exists($model, $method)) {
                $this->splittables[$class] = true;
                break;
            }
        }

        return $this->splittables[$class];
    }

    /**
     * @param  object $searchable
     * @param  array $array
     *
     * @return array
     */
    private function splitSearchable($searchable, array $array): array
    {
        $splittedBy = null;
        $pieces = [];
        foreach ($array as $key => $value) {
            $method = 'split'.Str::camel($key);
            $model = $searchable->getModel();
            if (method_exists($model, $method)) {
                $result = $model->{$method}($value);

                if (is_array($result)) {
                    $pieces = $result;
                } else {
                    if (is_string($result)) {
                        $pieces = (new $result)($value);
                    } else {
                        if (is_object($result)) {
                            $pieces = $result->__invoke($value);
                        }
                    }
                }
                $splittedBy = $key;
                break;
            }
        }

        return [$pieces, $splittedBy];
    }
}
