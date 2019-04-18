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

namespace Algolia\ScoutExtended\Splitters\HtmlSplitter;

/**
 * @internal
 */
final class NodesCollection
{
    /**
     * Collection of \Algolia\ScoutExtended\Splitters\HtmlSplitter\NodeCollection
     * and int as importance after each\Algolia\ScoutExtended\Splitters\HtmlSplitter\NodeCollection.
     *
     * @var array
     */
    private $nodesImportance = [];

    /**
     * String.
     */
    private const IMPORTANCE = 'importance';

    /**
     * Importance need to be add after to avoid polluted queue.
     *
     * @param \Algolia\ScoutExtended\Splitters\HtmlSplitter\NodeCollection $nodes
     *
     * @return void
     */
    public function push(NodeCollection $nodes): void
    {
        $this->nodesImportance[] = $nodes->getNodes();
        $this->nodesImportance[] = [self::IMPORTANCE => $nodes->importanceWeight($nodes->last(0))];
    }

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->nodesImportance as $nodes) {
            foreach ($nodes as $node) {
                if ($node instanceof Node) {
                    $object[$node->getTag()] = $node->getContent();
                } else {
                    $object[self::IMPORTANCE] = $node;
                    $array[] = $object;
                    $object = [];
                }
            }
        }

        return $array;
    }
}
