<?php

/**
 * Tracks worker progress output and updates the progress bar.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use Readalizer\Readalizer\Console\ProgressBar;

final class WorkerProgressReporter
{
    private const PROGRESS_MARKER = "\n";
    private const EMPTY_STRING = '';
    private const MODE_READ = 'rb';

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function updateProgress(WorkerProcess $process, ?ProgressBar $progress): void
    {
        if ($progress === null) {
            return;
        }

        $delta = $this->calculateProgressDelta($process);
        if ($delta <= 0) {
            return;
        }

        $process->getState()->addReportedCount($delta);
        $progress->addSteps($delta);
    }

    public function handleProgressClose(WorkerProcess $process): void
    {
        $process->getState()->setProgressHandle(null);
    }

    public function reportCompletion(?ProgressBar $progress): void
    {
        if ($progress === null) {
            return;
        }

        $progress->reportCompletion();
    }

    private function createProgressHandle(string $path): ?\SplFileObject
    {
        try {
            return new \SplFileObject($path, self::MODE_READ);
        } catch (\RuntimeException $exception) {
            return null;
        }
    }

    private function calculateProgressDelta(WorkerProcess $process): int
    {
        $handle = $this->getProgressHandle($process);
        if ($handle === null) {
            return 0;
        }

        $data = $handle->fread(8192);
        if ($data === false || $data === self::EMPTY_STRING) {
            return 0;
        }

        $count = substr_count($data, self::PROGRESS_MARKER);
        if ($count <= 0) {
            return 0;
        }

        $remaining = $process->getMetrics()->getFileCount() - $process->getState()->getReportedCount();
        if ($remaining <= 0) {
            return 0;
        }

        return min($count, $remaining);
    }

    private function getProgressHandle(WorkerProcess $process): ?\SplFileObject
    {
        $state = $process->getState();
        $handle = $state->getProgressHandle();
        if ($handle !== null) {
            return $handle;
        }

        $handle = $this->createProgressHandle($process->getPaths()->getProgressPath());
        if ($handle === null) {
            return null;
        }

        $state->setProgressHandle($handle);
        return $handle;
    }
}
