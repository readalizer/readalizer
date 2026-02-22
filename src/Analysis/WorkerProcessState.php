<?php

/**
 * Tracks worker progress state.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class WorkerProcessState
{
    private function __construct(
        private ?\SplFileObject $progressHandle,
        private int $reportedCount
    ) {
    }

    public static function create(?\SplFileObject $progressHandle, int $reportedCount): self
    {
        return new self($progressHandle, $reportedCount);
    }

    public function getProgressHandle(): ?\SplFileObject
    {
        return $this->progressHandle;
    }

    public function setProgressHandle(?\SplFileObject $handle): void
    {
        $this->progressHandle = $handle;
    }

    public function getReportedCount(): int
    {
        return $this->reportedCount;
    }

    public function addReportedCount(int $delta): void
    {
        $this->reportedCount += $delta;
    }
}
