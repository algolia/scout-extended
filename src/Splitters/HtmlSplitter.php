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
    const IMPORTANCE = 'importance';

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
     * @param array $object
     *
     * @return int
     */
    public function findWeight(array $object): int
    {
        return (int) array_search((key($object)), $this->nodes);
    }

    /**
     * Add object to queue.
     *
     * @param array $object
     * @param array $queue
     *
     * @return array
     */
    public function addObjectToQueue(array $object, array $queue): array
    {
        if (count($queue) == 0) {
            $queue[] = $object;

            return $queue;
        } else {
            if ($this->findWeight($object) > $this->findWeight(end($queue))) {
                $queue[] = $object;

                return $queue;
            } else {
                array_pop($queue);

                return $this->addObjectToQueue($object, $queue);
            }
        }
    }

    /**
     * Importance formula.
     * Give integer from tags ranking.
     *
     * @param \DOMElement $node
     * @param array $queue
     *
     * @return int
     */
    public function importanceWeight(\DOMElement $node, array $queue): int
    {
        if ($node->nodeName === 'p') {
            if (empty(end($queue))) {
                return 0;
            }

            return (int) (count($this->nodes) - 1) + (int) (array_search(key(end($queue)), $this->nodes));
        }

        return (int) array_search($node->nodeName, $this->nodes);
    }

    /**
     * Clean Records to have a correct format.
     *
     *
     * @param array $objects
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
        $xpath = new DOMXpath($dom);
        $queue = [];
        $objects = [];
        $xpathQuery = '//'.implode(' | //', $this->nodes);
        $nodes = $xpath->query($xpathQuery);

        foreach ($nodes as $node) {
            $object = [$node->nodeName => $node->textContent];
            $importance = $this->importanceWeight($node, $queue);
            $queue = $this->addObjectToQueue($object, $queue);
            $cloneQueue = $queue;
            $cloneQueue[] = [self::IMPORTANCE => $importance];
            $objects[] = $cloneQueue;
        }

        $records = $this->cleanRecords($objects);

        return $records;
    }
}
