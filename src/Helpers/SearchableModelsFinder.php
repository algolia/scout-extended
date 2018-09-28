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

namespace Algolia\ScoutExtended\Helpers;

use ReflectionClass;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Illuminate\Foundation\Application;
use Illuminate\Database\Eloquent\Model;

/**
 * @internal
 */
final class SearchableModelsFinder
{
    /**
     * @var array
     */
    private static $declaredClasses;

    /**
     * @var \Illuminate\Foundation\Application
     */
    private $app;

    /**
     * SearchableModelsFinder constructor.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get a list of searchable models.
     *
     * @return string[]
     */
    public function find(): array
    {
        $appNamespace = $this->app->getNamespace();

        return array_values(array_filter($this->getProjectClasses(), function (string $class) use ($appNamespace) {
            return Str::startsWith($class, $appNamespace) && $this->isSearchableModel($class);
        }));
    }

    /**
     * @param  string $class
     *
     * @return bool
     * @throws \ReflectionException
     */
    private function isSearchableModel($class): bool
    {
        $reflectionClass = new ReflectionClass($class);

        return $reflectionClass->isSubclassOf(Model::class) && method_exists($class, 'bootSearchable');
    }

    /**
     * @return array
     */
    private function getProjectClasses(): array
    {
        if (self::$declaredClasses === null) {
            $configFiles = Finder::create()->files()->name('*.php')->in($this->app->path());

            foreach ($configFiles->files() as $file) {
                require_once $file;
            }

            self::$declaredClasses = get_declared_classes();
        }

        return self::$declaredClasses;
    }
}
