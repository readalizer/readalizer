<?php

/**
 * A typed collection of file-level rule instances.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use ArrayIterator;
use IteratorAggregate;
use Millerphp\Readalizer\Contracts\FileRuleContract;

/**
 * A typed collection of file-level rule instances.
 */
/**
 * @implements IteratorAggregate<int, FileRuleContract>
 */
final class FileRuleCollection implements IteratorAggregate
{
    /** @param FileRuleContract[] $rules */
    private function __construct(private readonly array $rules)
    {
    }

    /** @param FileRuleContract[] $rules */
    public static function create(array $rules): self
    {
        return new self($rules);
    }

    /** @return ArrayIterator<int, FileRuleContract> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rules);
    }

    public function count(): int
    {
        return count($this->rules);
    }
}
