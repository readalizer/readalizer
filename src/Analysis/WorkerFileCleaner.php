<?php

/**
 * Removes temporary files created for worker processes.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class WorkerFileCleaner
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function removeFiles(WorkerProcess $process): void
    {
        $paths = $process->getPaths();
        @unlink($paths->getFilesPath());
        @unlink($paths->getOutputPath());
        @unlink($paths->getProgressPath());
    }
}
