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

namespace Algolia\ScoutExtended\Exceptions;

use RuntimeException;

/**
 * @internal
 */
final class ModelNotFoundException extends RuntimeException
{
    /**
     * Name of the affected model.
     *
     * @var string
     */
    private $model;

    /**
     * Sets the effected model.
     *
     * @param string $model
     *
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    /**
     * Get the effected model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }
}
