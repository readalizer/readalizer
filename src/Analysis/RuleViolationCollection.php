<?php

/**
 * A typed collection of rule violations produced by the analyser.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use IteratorAggregate;
use ArrayIterator;

/**
 * A collection of RuleViolation objects.
 *
 * This value object provides a type-safe and iterable way to manage
 * a set of rule violations.
 */
/**
 * @implements IteratorAggregate<int, RuleViolation>
 */
final class RuleViolationCollection implements IteratorAggregate
{
    /**
     * @param RuleViolation[] $violations
     */
    private function __construct(private readonly array $violations)
    {
    }

    /**
     * Creates a new RuleViolationCollection instance from an array of RuleViolation objects.
     *
     * @param RuleViolation[] $violations The array of rule violations.
     */
    public static function create(array $violations): self
    {
        return new self($violations);
    }

    /**
     * Returns an iterator for the violations in the collection.
     */
    /** @return ArrayIterator<int, RuleViolation> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->violations);
    }

    public function merge(self $other): self
    {
        return new self(array_merge($this->violations, $other->violations));
    }

    /**
     * Returns the number of violations in the collection.
     */
    public function count(): int
    {
        return count($this->violations);
    }
}
