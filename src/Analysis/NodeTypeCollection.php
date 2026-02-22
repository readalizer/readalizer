<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use ArrayIterator;
use IteratorAggregate;
use PhpParser\Node;

/**
 * @template TNode of Node
 * @implements IteratorAggregate<int, class-string<TNode>>
 */
final class NodeTypeCollection implements IteratorAggregate
{
    /**
     * @param array<int, class-string<TNode>> $types
     */
    private function __construct(private readonly array $types) {}

    /**
     * @param array<int, class-string<TNode>> $types
     * @return self<TNode>
     */
    public static function create(array $types): self
    {
        return new self($types);
    }

    /** @return ArrayIterator<int, class-string<TNode>> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->types);
    }
}
