<?php

/**
 * A typed collection of rule instances for the analyser.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use IteratorAggregate;
use ArrayIterator;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use Millerphp\Readalizer\Contracts\RuleContract;

/**
 * A collection of RuleContract and FileRuleContract instances.
 *
 * This value object provides a type-safe and iterable way to manage
 * a set of rules within the application.
 */
/**
 * @implements IteratorAggregate<int, RuleContract<\PhpParser\Node>|FileRuleContract>
 */
final class RuleCollection implements IteratorAggregate
{
    /**
     * @param array<int, RuleContract<\PhpParser\Node>|FileRuleContract> $rules
     */
    private function __construct(private readonly array $rules)
    {
    }

    /**
     * Creates a new RuleCollection instance from an array of rules.
     *
     * @param array<int, RuleContract<\PhpParser\Node>|FileRuleContract> $rules The array of rules.
     */
    public static function create(array $rules): self
    {
        return new self($rules);
    }

    /**
     * Returns an iterator for the rules in the collection.
     */
    /** @return ArrayIterator<int, RuleContract<\PhpParser\Node>|FileRuleContract> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rules);
    }

    public function merge(self $other): self
    {
        return new self(array_merge($this->rules, $other->rules));
    }

    public function getNodeRules(): NodeRuleCollection
    {
        return NodeRuleCollection::create(
            array_values(array_filter($this->rules, fn($r) => $r instanceof RuleContract))
        );
    }

    public function getFileRules(): FileRuleCollection
    {
        return FileRuleCollection::create(
            array_values(array_filter($this->rules, fn($r) => $r instanceof FileRuleContract))
        );
    }

    /**
     * Returns the number of rules in the collection.
     */
    public function count(): int
    {
        return count($this->rules);
    }
}
