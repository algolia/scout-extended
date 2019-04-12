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

/**
 * Class HtmlSplitter
 *
 * @package Algolia\ScoutExtended\Splitters
 */
class HtmlSplitter implements SplitterContract
{
    /**
     * The list of html tags.
     *
     * @var string[]
     */
    protected $nodes = [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'p',
    ];

    /**
     * String for key check purpose.
     *
     * @const string IMPORTANCE
     */
    private const IMPORTANCE = 'importance';

    /**
     * String for exception purpose.
     *
     * @const string PARAGRAPH
     */
    private const PARAGRAPH = 'p';

    /**
     * Creates a new instance of the class.
     *
     * @param array $nodes
     *
     * @return void
     */
    public function __construct(array $nodes = null)
    {
        if ($nodes !== null) {
            $this->nodes = $nodes;
        }
    }

    /**
     * Find weight of current nodes.
     *
     * @param array<string, string> $object
     *
     * @return int
     */
    public function findWeight(array $object): int
    {
        return (int) array_search((key($object)), $this->nodes, true);
    }

    /**
     * Add object to queue.
     *
     * @param array<array<string, string>> $object
     * @param array $queue
     *
     * @return array
     */
    public function addObjectToQueue(array $object, array $queue): array
    {
        if (count($queue) === 0) {
            $queue[] = $object;
            return $queue;
        }

        if ($this->findWeight($object) > $this->findWeight(end($queue))) {
            $queue[] = $object;
            return $queue;
        }

        array_pop($queue);
        return $this->addObjectToQueue($object, $queue);
    }


    /**
     * Importance formula.
     * Give integer from tags ranking.
     *
     * @param \DOMElement $node
     * @param array<array<array<string, string>, <array<string, int>>> $queue
     *
     * @return int
     */
    public function importanceWeight(\DOMElement $node, array $queue): int
    {
        if ($node->nodeName === self::PARAGRAPH) {
            if (empty(end($queue))) {
                return 0;
            }
            if (key(end($queue)) === self::PARAGRAPH) {
                $key = key(prev($queue));
            } else {
                $key = key(end($queue));
            }

            return (int) (count($this->nodes) - 1) + (int) array_search($key, $this->nodes, true);
        }

        return (int) array_search($node->nodeName, $this->nodes, true);
    }

    /**
     * Clean Records to have a correct format.
     *
     *
     * @param array<array<array<string, string>, <array<string, int>>> $objects
     *
     * @return array
     */
    public function cleanRecords(array $objects): array
    {
        $records = [];
        foreach ($objects as $object) {
            foreach ($object as $data) {
                foreach ($data as $key => $value) {
                    $record[$key] = $value;
                    if ($key === self::IMPORTANCE) {
                        $records[] = $record;
                        $record = [];
                    }
                }
            }
        }

        return $records;
    }

    /**
     * Clean Content from Html tag.
     * Remove space at the begin and end, useless space, return
     *
     * @param string $content
     *
     * @return string
     */
    public function cleanContent(string $content): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace('\n', '', $content)));
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
        try {
            $dom->loadHTML($value);
        } catch (\ErrorException $exception) {
        }

        $xpath = new DOMXpath($dom);
        $queue = [];
        $objects = [];
        $xpathQuery = '//' . implode(' | //', $this->nodes);
        $nodes = $xpath->query($xpathQuery);

        foreach ($nodes as $node) {
            $content = $this->cleanContent($node->textContent);
            $object = [$node->nodeName => $content];
            $importance = $this->importanceWeight($node, $queue);
            $queue = $this->addObjectToQueue($object, $queue);
            $cloneQueue = $queue;
            $cloneQueue[] = [self::IMPORTANCE => $importance];
            $objects[] = $cloneQueue;
        }
        return $this->cleanRecords($objects);
    }
}
