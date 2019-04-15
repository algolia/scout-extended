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

namespace Algolia\ScoutExtended\Splitters\HtmlSplitterComponent;

final class Queue
{
    /**
     * @var array
     */
    protected $queue = [];
    protected $cloneQueue = [];

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
        'h6',
        'p',
    ];

    /**
     * String for key check purpose.
     */
    private const IMPORTANCE = 'importance';

    /**
     * String for exception purpose.
     */
    private const PARAGRAPH = 'p';

    /**
     * @return array
     */
    public function getCloneQueue(): array
    {
        return $this->cloneQueue;
    }

    /**
     * Add object to queue.
     *
     * @param ObjectQueue $object
     */
    public function addObjectQueue(ObjectQueue $object): void
    {
        if ($this->lengthQueue() === 0) {
            $this->queue[] = $object;
            $this->cloneQueue();
        } elseif ($this->findWeight($object) > $this->findWeight(end($this->queue))) {
            $this->queue[] = $object;
            $this->cloneQueue();
        } else {
            array_pop($this->queue);
            $this->addObjectQueue($object);
        }
    }

    /**
     * Clean Records to have a correct format.
     *
     * @return array
     */
    public function sanitizeQueue(): array
    {
        $records = [];
        foreach ($this->cloneQueue as $queue) {
            foreach ($queue as $object) {
                if ($object instanceof ObjectQueue) {
                    $record[$object->getTag()] = $object->getTagContent();
                } else {
                    $record[self::IMPORTANCE] = $object;
                    $records[] = $record;
                    $record = [];
                }
            }
        }

        return $records;
    }

    /**
     * Importance need to be add after to avoid polluted queue.
     *
     * @return void
     */
    private function cloneQueue(): void
    {
        $this->cloneQueue[] = $this->queue;
        $this->cloneQueue[] = [self::IMPORTANCE => $this->importanceWeight(end($this->queue))];
    }

    /**
     * Importance formula.
     * Give integer from tags ranking.
     *
     * @param ObjectQueue $objectQueue
     *
     * @return int
     */
    private function importanceWeight(ObjectQueue $objectQueue): int
    {
        if ($objectQueue->getTag() === self::PARAGRAPH) {
            $object = prev($this->queue);
            if (empty(end($this->queue)) || $this->lengthQueue() === 1) {
                return 0;
            }

            return (count($this->tags) - 1) + $this->findWeight($object);
        }

        return $this->findWeight($objectQueue);
    }

    /**
     * Find weight of current nodes.
     *
     * @param ObjectQueue $object
     *
     * @return int
     */
    private function findWeight(ObjectQueue $object): int
    {
        return (int) array_search($object->getTag(), $this->tags, true);
    }

    /**
     * Give the length of the queue.
     *
     * @return int
     */
    private function lengthQueue(): int
    {
        return count($this->queue);
    }
}
