<?php

/**
 * Stores worker runtime metrics.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class WorkerProcessRuntime
{
    private function __construct(
        private readonly float $startTime,
        private readonly int $fileCount
    ) {
    }

    public static function create(float $startTime, int $fileCount): self
    {
        return new self($startTime, $fileCount);
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getFileCount(): int
    {
        return $this->fileCount;
    }
}
