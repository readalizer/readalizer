<?php

/**
 * Collects decoded payload items for violations.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, array<string, mixed>>
 */
final class PayloadItemCollection implements IteratorAggregate
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function __construct(private readonly array $items)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public static function create(array $items): self
    {
        return new self($items);
    }

    /**
     * @return ArrayIterator<int, array<string, mixed>>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
