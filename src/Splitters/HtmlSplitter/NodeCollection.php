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
final class NodeCollection
{
    /**
     * Collection of \Algolia\ScoutExtended\Splitters\HtmlSplitter\Node.
     *
     * @var array
     */
    private $nodes = [];

    /**
     * @var \Algolia\ScoutExtended\Splitters\HtmlSplitter\NodesCollection.
     */
    private $nodesCollection;
    /**
     * The list of html tags.
     *
     * @var string[]
     */
    private $tags = [];

    /**
     * String.
     */
    private const PARAGRAPH = 'p';

    /**
     * NodeCollection constructor.
     *
     * @param array|null $tags
     * @param \Algolia\ScoutExtended\Splitters\HtmlSplitter\NodesCollection $nodesCollection
     */
    public function __construct(array $tags = null, NodesCollection $nodesCollection)
    {
        if ($tags !== null) {
            $this->tags = $tags;
        }
        $this->nodesCollection = $nodesCollection;
    }

    /**
     * @return array
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Add object to collection.
     *
     * @param Node $node
     */
    public function push(Node $node): void
    {
        if ($this->lengthNodes() === 0) {
            $this->nodes[] = $node;
            $this->nodesCollection->push($this);
        } elseif ($this->findWeight($node) > $this->findWeight($this->last(0))) {
            $this->nodes[] = $node;
            $this->nodesCollection->push($this);
        } else {
            array_pop($this->nodes);
            $this->push($node);
        }
    }

    /**
     * Return the last element of the collection
     * Give integer as pointer from the end.
     *
     * @param int $position
     *
     * @return \Algolia\ScoutExtended\Splitters\HtmlSplitter\Node
     */
    public function last(int $position): Node
    {
        return $this->nodes[$this->lengthNodes() - $position - 1];
    }

    /**
     * Importance formula.
     * Give integer from tags ranking.
     *
     * @param \Algolia\ScoutExtended\Splitters\HtmlSplitter\Node $node
     *
     * @return int
     */
    public function importanceWeight(Node $node): int
    {
        if ($node->getTag() === self::PARAGRAPH) {
            if ($this->last(1) === null || $this->lengthNodes() === 1) {
                return 0;
            }
            $object = $this->last(1);

            return (count($this->tags) - 1) + $this->findWeight($object);
        }

        return $this->findWeight($node);
    }

    /**
     * Find weight of current nodes.
     *
     * @param \Algolia\ScoutExtended\Splitters\HtmlSplitter\Node $node
     *
     * @return int
     */
    private function findWeight(Node $node): int
    {
        return (int) array_search($node->getTag(), $this->tags, true);
    }

    /**
     * Give the length of the collection.
     *
     * @return int
     */
    private function lengthNodes(): int
    {
        return count($this->nodes);
    }
}
