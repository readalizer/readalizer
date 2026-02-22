<?php

/**
 * Collection of worker processes.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, WorkerProcess>
 */
final class WorkerProcessCollection implements IteratorAggregate
{
    /**
     * @param array<int, WorkerProcess> $processes
     */
    private function __construct(private array $processes)
    {
    }

    /**
     * @param array<int, WorkerProcess> $processes
     */
    public static function create(array $processes): self
    {
        return new self($processes);
    }

    public function addProcess(WorkerProcess $process): void
    {
        $this->processes[] = $process;
    }

    public function removeProcess(int $index): void
    {
        unset($this->processes[$index]);
    }

    public function isEmpty(): bool
    {
        return $this->processes === [];
    }

    /** @return ArrayIterator<int, WorkerProcess> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->processes);
    }

    public function count(): int
    {
        return count($this->processes);
    }
}
