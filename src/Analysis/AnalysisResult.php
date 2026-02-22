<?php

/**
 * Represents the result of a code analysis, containing a collection of RuleViolation objects.
 *
 * This class provides a type-safe way to return analysis results, encapsulating
 * the list of violations and offering methods to iterate over them.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use IteratorAggregate;
use ArrayIterator;

/**
 * @implements IteratorAggregate<int, RuleViolation>
 */
final class AnalysisResult implements IteratorAggregate
{
    private function __construct(private readonly RuleViolationCollection $violations) {}

    public static function create(RuleViolationCollection $violations): self
    {
        return new self($violations);
    }

    /** @return ArrayIterator<int, RuleViolation> */
    public function getIterator(): ArrayIterator
    {
        return $this->violations->getIterator();
    }



    public function count(): int
    {
        return $this->violations->count();
    }

    public function getRuleViolationCollection(): RuleViolationCollection
    {
        return $this->violations;
    }
}
