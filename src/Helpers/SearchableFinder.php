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

use function in_array;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Illuminate\Foundation\Application;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class SearchableFinder
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
     * Get a list of searchable models from the given command.
     *
     * @param \Illuminate\Console\Command $command
     *
     * @return array
     */
    public function fromCommand(Command $command): array
    {
        $searchables = (array) $command->argument('model');

        if (empty($searchables) && empty($searchables = $this->find())) {
            throw new InvalidArgumentException('No searchable models found. Please add the ['.Searchable::class.'] trait to a model.');
        }

        return $searchables;
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
     */
    private function isSearchableModel($class): bool
    {
        return in_array(Searchable::class, class_uses_recursive($class), true);
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
