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
 * @package Algolia\ScoutExtended\Splitters\HtmlSplitter
 * @internal
 */
final class NodeCollection
{
    /**
     * Collection of \Algolia\ScoutExtended\Splitters\HtmlSplitter\Node
     *
     * @var array
     */
    private $nodes = [];

    /**
     * Clone of \Algolia\ScoutExtended\Splitters\HtmlSplitter\NodeCollection
     *
     * @var array
     */
    private $cloneNodes = [];

    /**
     * The list of html tags.
     *
     * @var string[]
     */
    private $tags = [];

    /**
     * String
     */
    private const IMPORTANCE = 'importance';

    /**
     * String
     */
    private const PARAGRAPH = 'p';

    /**
     * NodeCollection constructor.
     *
     * @param array|null $tags
     */
    public function __construct(array $tags = null)
    {
        if ($tags !== null) {
            $this->tags = $tags;
        }
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
            $this->cloneNodes();
        } else if ($this->findWeight($node) > $this->findWeight(end($this->nodes))) {
            $this->nodes[] = $node;
            $this->cloneNodes();
        } else {
            array_pop($this->nodes);
            $this->push($node);
        }
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->cloneNodes as $nodes) {
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

    /**
     * Importance need to be add after to avoid polluted queue.
     *
     * @return void
     */
    private function cloneNodes(): void
    {
        $this->cloneNodes[] = $this->nodes;
        $this->cloneNodes[] = [self::IMPORTANCE => $this->importanceWeight(end($this->nodes))];
    }

    /**
     * Importance formula.
     * Give integer from tags ranking.
     *
     * @param Node $node
     *
     * @return int
     */
    private function importanceWeight(Node $node): int
    {
        if ($node->getTag() === self::PARAGRAPH) {
            $object = prev($this->nodes);
            if (empty(end($this->nodes)) || $this->lengthNodes() === 1) {
                return 0;
            }

            return (count($this->tags) - 1) + $this->findWeight($object);
        }

        return $this->findWeight($node);
    }

    /**
     * Find weight of current nodes.
     *
     * @param Node $node
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
