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

namespace Algolia\ScoutExtended\Splitters;

use DOMDocument;
use Algolia\ScoutExtended\Contracts\SplitterContract;

class HtmlSplitter implements SplitterContract
{
    /**
     * The list of html tags.
     *
     * @var string[]
     */
    protected $tags = [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'p',
    ];

    /**
     * Creates a new instance of the class.
     *
     * @param array $tags
     *
     * @return void
     */
    public function __construct(array $tags = null)
    {
        if ($tags !== null) {
            $this->tags = $tags;
        }
    }

    /**
     * Acts a static factory.
     *
     * @param  string|array $tags
     *
     * @return static
     */
    public static function by($tags)
    {
        return new static((array) $tags);
    }

    /**
     * Splits the given value.
     *
     * @param  object $searchable
     * @param  string $value
     *
     * @return array
     */
    public function split($searchable, $value): array
    {
        $dom = new DOMDocument();
        $dom->loadHTML($value);
        $values = [];

        foreach ($this->tags as $tag) {
            foreach ($dom->getElementsByTagName($tag) as $node) {
                $values[] = $node->textContent;

                while (($node = $node->nextSibling) && $node->nodeName !== $tag) {
                    $values[] = $node->textContent;
                }
            }
        }

        return $values;
    }
}
