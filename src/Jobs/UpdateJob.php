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

namespace Algolia\ScoutExtended\Jobs;

use function in_array;
use function is_array;
use function get_class;
use function is_object;
use function is_string;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Algolia\AlgoliaSearch\SearchClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Algolia\ScoutExtended\Searchable\ObjectIdEncrypter;

/**
 * @internal
 */
final class UpdateJob
{
    /**
     * Contains a list of splittables searchables.
     *
     * Example: [
     *      '\App\Thread' => true,
     *      '\App\User' => false,
     * ];
     *
     * @var array
     */
    private $splittables = [];

    /**
     * @var \Illuminate\Support\Collection
     */
    private $searchables;

    /**
     * UpdateJob constructor.
     *
     * @param \Illuminate\Support\Collection $searchables
     *
     * @return void
     */
    public function __construct(Collection $searchables)
    {
        $this->searchables = $searchables;
    }

    /**
     * @param \Algolia\AlgoliaSearch\SearchClient $client
     *
     * @return void
     */
    public function handle(SearchClient $client): void
    {
        if ($this->searchables->isEmpty()) {
            return;
        }

        if (config('scout.soft_delete', false) && $this->usesSoftDelete($this->searchables->first())) {
            $this->searchables->each->pushSoftDeleteMetadata();
        }

        $index = $client->initIndex($this->searchables->first()->searchableAs());

        $objectsToSave = [];
        $searchablesToDelete = [];

        foreach ($this->searchables as $key => $searchable) {
            if (empty($array = array_merge($searchable->toSearchableArray(), $searchable->scoutMetadata()))) {
                continue;
            }

            $array = $this->mutateArray($searchable, $array);

            $array['_tags'] = (array) ($array['_tags'] ?? []);

            array_push($array['_tags'], ObjectIdEncrypter::encrypt($searchable));

            if ($this->shouldBeSplitted($searchable)) {
                $objects = $this->splitSearchable($searchable, $array);

                foreach ($objects as $part => $object) {
                    $object['objectID'] = ObjectIdEncrypter::encrypt($searchable, (int) $part);
                    $objectsToSave[] = $object;
                }
                $searchablesToDelete[] = $searchable;
            } else {
                $array['objectID'] = ObjectIdEncrypter::encrypt($searchable);
                $objectsToSave[] = $array;
            }
        }

        dispatch_now(new DeleteJob(collect($searchablesToDelete)));

        $result = $index->saveObjects($objectsToSave);
        if (config('scout.synchronous', false)) {
            $result->wait();
        }
    }

    /**
     * @param  object $searchable
     *
     * @return bool
     */
    private function shouldBeSplitted($searchable): bool
    {
        $class = get_class($searchable->getModel());

        if (! array_key_exists($class, $this->splittables)) {
            $this->splittables[$class] = false;

            foreach ($searchable->toSearchableArray() as $key => $value) {
                $method = 'split'.Str::camel($key);
                $model = $searchable->getModel();
                if (method_exists($model, $method)) {
                    $this->splittables[$class] = true;
                    break;
                }
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
        $pieces = [];
        foreach ($array as $key => $value) {
            $method = 'split'.Str::camel((string) $key);
            $model = $searchable->getModel();
            if (method_exists($model, $method)) {
                $result = $model->{$method}($value);
                $splittedBy = $key;
                $pieces[$splittedBy] = [];
                switch (true) {
                    case is_array($result):
                        $pieces[$splittedBy] = $result;
                        break;
                    case is_string($result):
                        $pieces[$splittedBy] = app($result)($model, $value);
                        break;
                    case is_object($result):
                        $pieces[$splittedBy] = $result->__invoke($model, $value);
                        break;
                }
            }
        }

        $objects = [[]];
        foreach ($pieces as $splittedBy => $values) {
            $temp = [];
            foreach ($objects as $object) {
                foreach ($values as $value) {
                    $temp[] = array_merge($object, [$splittedBy => $value]);
                }
            }
            $objects = $temp;
        }

        return array_map(function ($object) use ($array) {
            return array_merge($array, $object);
        }, $objects);
    }

    /**
     * Determine if the given searchable uses soft deletes.
     *
     * @param  object $searchable
     *
     * @return bool
     */
    private function usesSoftDelete($searchable): bool
    {
        return $searchable instanceof Model && in_array(SoftDeletes::class, class_uses_recursive($searchable), true);
    }

    /**
     * Mutate the given array using searchable's model attributes.
     *
     * @param  object  $searchable
     * @param  array  $array
     *
     * @return array
     */
    private function mutateArray($searchable, array $array): array
    {
        foreach ($array as $key => $value) {
            $attributeValue = $searchable->getModel()->getAttribute($key);

            /*
             * Casts carbon instances to timestamp.
             */
            if ($attributeValue instanceof \Illuminate\Support\Carbon) {
                $array[$key] = $attributeValue->getTimestamp();
            }

            /*
             * Casts numeric strings to integers/floats.
             */
            if (is_string($attributeValue) && is_numeric($attributeValue)) {
                $array[$key] = ctype_digit($attributeValue)
                    ? (int) $attributeValue
                    : (float) $attributeValue;
            }
        }

        return $array;
    }
}
