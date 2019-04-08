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
    protected $acceptedNodes = [
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
     * @param array $acceptedNodes
     *
     * @return void
     */
    public function __construct(array $acceptedNodes = null)
    {
        if ($acceptedNodes !== null) {
            $this->acceptedNodes = $acceptedNodes;
        }
    }

    /**
     * Find it's value in $acceptedNodes.
     *
     * @param array $object
     *
     * @return int
     */
    public function findValue($object): int
    {
        return array_search((key($object)), $this->acceptedNodes);
    }

    /**
     * Add object to queue.
     *
     * @param array $object
     * @param array $queue
     *
     * @return array
     */
    public function addObjectToQueue($object, $queue): array
    {
        if (count($queue) == 0) {
            $queue[] = $object;

            return $queue;
        } else {

            if ($this->findValue($object) > $this->findValue(end($queue))) {
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
    public function importanceWeight($node, $queue): int
    {
        if ($node->nodeName == 'p') {
            if (empty(end($queue))) {
                return 0;
            }

            return (count($this->acceptedNodes) - 1) + (array_search(key(end($queue)), $this->acceptedNodes));
        }

        return array_search($node->nodeName, $this->acceptedNodes);
    }

    /**
     * Clean Records to have a correct format.
     *
     *
     * @param array $records
     *
     * @return array
     */
    public function cleanRecords($records): array
    {
        $newRecords = [];
        foreach ($records as $record) {
            foreach ($record as $r) {
                foreach ($r as $res => $values) {
                    $newRecord[$res] = $values;
                    if ($res == 'importance') {
                        $newRecords[] = $newRecord;
                        $newRecord = [];
                    }
                }
            }
        }

        return $newRecords;
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
        $xpathQuery = '//'.implode(' | //', $this->acceptedNodes);
        $nodes = $xpath->query($xpathQuery);

        foreach ($nodes as $node) {
            $object = [$node->nodeName => $node->textContent];
            $importance = $this->importanceWeight($node, $queue);
            $queue = $this->addObjectToQueue($object, $queue);
            $cloneQueue = $queue;
            $cloneQueue[] = ['importance' => $importance];
            $records[] = $cloneQueue;
        }

        $records = $this->cleanRecords($records);

        return $records;
    }
}
