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

final class ObjectQueue
{
    private $tag;
    private $tagContent;

    /**
     * Object constructor.
     *
     * @param string $tag
     * @param string $tagContent
     */
    public function __construct(string $tag, string $tagContent)
    {
        $this->tag = $tag;
        $this->tagContent = $this->cleanTagContent($tagContent);
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    public function getTagContent(): string
    {
        return $this->tagContent;
    }

    /**
     *
     * Clean Content from Html tag.
     * Remove space at the begin and end, useless space, return.
     *
     * @param string $tagContent
     *
     * @return string
     */
    private function cleanTagContent(string $tagContent): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace('\n', '', $tagContent)));
    }
}
