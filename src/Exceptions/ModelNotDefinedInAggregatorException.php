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

use Throwable;
use RuntimeException;

final class ModelNotDefinedInAggregatorException extends RuntimeException
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = 'Model not defined in aggregator.';
        }

        parent::__construct($message, $code, $previous);
    }
}
