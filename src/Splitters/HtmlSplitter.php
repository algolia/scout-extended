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

use DOMXPath;
use DOMDocument;
use Algolia\ScoutExtended\Contracts\SplitterContract;
use Algolia\ScoutExtended\Splitters\HtmlSplitter\Node;
use Algolia\ScoutExtended\Contracts\SettingsUpdaterContract;
use Algolia\ScoutExtended\Splitters\HtmlSplitter\NodeCollection;
use Algolia\ScoutExtended\Splitters\HtmlSplitter\NodesCollection;

final class HtmlSplitter implements SplitterContract, SettingsUpdaterContract
{
    /**
     * The list of html tags.
     *
     * @var string[]
     */
    private $tags = [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
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
     * @param string|array<string> $tags
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
     * @param object $searchable
     * @param string $value
     *
     * @return array
     */
    public function split($searchable, $value): array
    {
        $dom = new DOMDocument();
        //DOMDocument is only for HTML4, this exception is too avoid errors from HTML5
        try {
            $dom->loadHTML($value);
        } catch (\ErrorException $exception) {
        }

        $xpath = new DOMXpath($dom);
        $xpathQuery = '//'.implode(' | //', $this->tags);
        $nodes = $xpath->query($xpathQuery);
        $nodesCollection = new NodesCollection();
        $nodeCollection = new NodeCollection($this->tags, $nodesCollection);

        foreach ($nodes as $node) {
            $nodeCollection->push(new Node($node->nodeName, $node->textContent));
        }

        return $nodesCollection->toArray();
    }

    /**
     * Returns the updated version of the given settings.
     *
     * @param array $settings
     * @param string $attribute
     *
     * @return array
     */
    public function updateSettings(array $settings, string $attribute): array
    {
        $settings['customRanking'][] = 'asc('.$attribute.'.importance)';

        return $settings;
    }
}
