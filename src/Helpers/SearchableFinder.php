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

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function in_array;
use Laravel\Scout\Searchable;
use RuntimeException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Finder\Finder;

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
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    /**
     * SearchableModelsFinder constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct($app)
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
        $searchables = (array) $command->argument('searchable');

        if (empty($searchables) && empty($searchables = $this->find())) {
            throw new InvalidArgumentException('No searchable classes found.');
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
        [$sources, $namespaces] = $this->inferProjectSourcePaths();

        return array_values(array_filter(
            $this->getProjectClasses($sources), function (string $class) use ($namespaces) {
                return Str::startsWith($class, $namespaces) && $this->isSearchableModel($class);
            }
        ));
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
     * @param array $sources
     * @return array
     */
    private function getProjectClasses(array $sources): array
    {
        if (self::$declaredClasses === null) {
            $configFiles = Finder::create()
                ->files()
                ->notName('*.blade.php')
                ->name('*.php')
                ->in($sources);

            foreach ($configFiles->files() as $file) {
                require_once $file;
            }

            self::$declaredClasses = get_declared_classes();
        }

        return self::$declaredClasses;
    }

    /**
     * Using the laravel project's composer.json retrieve the PSR-4 autoload to determine
     * the paths to search and namespaces to check against.
     *
     * @return array [$sources, $namespaces]
     */
    private function inferProjectSourcePaths(): array
    {
        if (! ($composer = file_get_contents(base_path('composer.json')))) {
            throw new RuntimeException('Error reading composer.json');
        }
        $autoload = json_decode($composer, true)['autoload'] ?? [];

        if (! isset($autoload['psr-4'])) {
            throw new RuntimeException('psr-4 autoload mappings are not present in composer.json');
        }

        $psr4 = collect($autoload['psr-4']);

        $sources = $psr4->values()->map(function ($path) {
            return base_path($path);
        })->toArray();
        $namespaces = $psr4->keys()->toArray();

        return [$sources, $namespaces];
    }
}
