<?php

/**
 * A collection of file paths to be analysed.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use IteratorAggregate;
use ArrayIterator;

/**
 * A collection of file paths for analysis.
 *
 * This value object encapsulates a list of paths, providing a type-safe and
 * iterable way to manage them within the analysis process.
 */
/**
 * @implements IteratorAggregate<int, string>
 */
final class PathCollection implements IteratorAggregate
{
    /**
     * @param array<int, string> $paths
     */
    private function __construct(private readonly array $paths)
    {
    }

    /**
     * Creates a new PathCollection instance from an array of paths.
     *
     * @param string[] $paths The array of file paths.
     */
    public static function create(array $paths): self
    {
        return new self(array_values($paths));
    }

    /**
     * Returns an iterator for the paths in the collection.
     */
    /** @return ArrayIterator<int, string> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->paths);
    }

    /**
     * Returns the number of paths in the collection.
     */
    public function count(): int
    {
        return count($this->paths);
    }
}
