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

namespace Algolia\ScoutExtended\Settings;

use Illuminate\Support\Facades\File;
use Riimu\Kit\PHPEncoder\PHPEncoder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\View\Factory as ViewFactory;

/**
 * @internal
 */
final class Compiler
{
    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var string[]
     */
    private static $rawTags = ['{!!', '!!}'];

    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $viewFactory;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    /**
     * @var \Riimu\Kit\PHPEncoder\PHPEncoder
     */
    private $encoder;

    /**
     * Compiler constructor.
     *
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Riimu\Kit\PHPEncoder\PHPEncoder $encoder
     *
     * @return void
     */
    public function __construct(ViewFactory $viewFactory, Filesystem $files, PHPEncoder $encoder)
    {
        $this->viewFactory = $viewFactory;
        $this->files = $files;
        $this->encoder = $encoder;
    }

    /**
     * Compiles the provided settings into the provided path.
     *
     * @param \Algolia\ScoutExtended\Settings\Settings $settings
     * @param string $path
     *
     * @return void
     */
    public function compile(Settings $settings, string $path): void
    {
        $viewVariables = self::getViewVariables();

        $viewParams = [];
        $all = $settings->all();

        foreach ($viewVariables as $viewVariable) {
            if (array_key_exists($viewVariable, $all)) {
                $viewParams[$viewVariable] = $this->encoder->encode($all[$viewVariable], ['array.base' => 4]);
            }
        }

        $indexChangedSettings = [];
        foreach ($settings->changed() as $setting => $value) {
            if (! array_key_exists($setting, $viewParams)) {
                $indexChangedSettings[$setting] = $value;
            }
        }

        $viewParams['__indexChangedSettings'] = $this->encoder->encode($indexChangedSettings, ['array.base' => 0]);

        if (empty($indexChangedSettings)) {
            $viewParams['__indexChangedSettings'] = ']';
        } else {
            $viewParams['__indexChangedSettings'] = preg_replace('/^.+\n/', '', $viewParams['__indexChangedSettings']);
        }

        $this->files->put($path, '<?php

'.$this->viewFactory->make('algolia::config', $viewParams)->render());
    }

    /**
     * Returns the view variables.
     *
     * @return array
     */
    public static function getViewVariables(): array
    {
        $contents = File::get(__DIR__.'/../../resources/views/config.blade.php');
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', self::$rawTags[0], self::$rawTags[1]);
        preg_match_all($pattern, $contents, $matches);

        array_pop($matches[2]);

        return array_map(function ($match) {
            return ltrim(explode(' ', $match)[0], '$');
        }, $matches[2]);
    }
}
