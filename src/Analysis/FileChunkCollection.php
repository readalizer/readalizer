<?php

/**
 * Collection of file chunks for worker dispatch.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, PathCollection>
 */
final class FileChunkCollection implements IteratorAggregate
{
    /**
     * @param array<int, PathCollection> $chunks
     */
    private function __construct(private array $chunks)
    {
    }

    /**
     * @param array<int, PathCollection> $chunks
     */
    public static function create(array $chunks): self
    {
        return new self($chunks);
    }

    public function isEmpty(): bool
    {
        return $this->chunks === [];
    }

    /** @return ArrayIterator<int, PathCollection> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->chunks);
    }

    public function count(): int
    {
        return count($this->chunks);
    }
}
