<?php

/**
 * A typed collection of node-level rule instances.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use ArrayIterator;
use IteratorAggregate;
use Readalizer\Readalizer\Contracts\RuleContract;

/**
 * A typed collection of node-level rule instances.
 */
/**
 * @implements IteratorAggregate<int, RuleContract<\PhpParser\Node>>
 */
final class NodeRuleCollection implements IteratorAggregate
{
    /** @param array<int, RuleContract<\PhpParser\Node>> $rules */
    private function __construct(private readonly array $rules)
    {
    }

    /** @param array<int, RuleContract<\PhpParser\Node>> $rules */
    public static function create(array $rules): self
    {
        return new self($rules);
    }

    /** @return ArrayIterator<int, RuleContract<\PhpParser\Node>> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rules);
    }

    public function count(): int
    {
        return count($this->rules);
    }
}
