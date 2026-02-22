<?php

/**
 * Aggregates worker process helper services.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class WorkerProcessServiceBundle
{
    private function __construct(
        private readonly WorkerProgressReporter $progressReporter,
        private readonly WorkerResultReader $resultReader,
        private readonly WorkerFileCleaner $fileCleaner,
        private readonly WorkerProcessTerminator $processTerminator
    ) {
    }

    public static function create(
        WorkerProgressReporter $progressReporter,
        WorkerResultReader $resultReader,
        WorkerFileCleaner $fileCleaner,
        WorkerProcessTerminator $processTerminator
    ): self {
        return new self($progressReporter, $resultReader, $fileCleaner, $processTerminator);
    }

    public function getProgressReporter(): WorkerProgressReporter
    {
        return $this->progressReporter;
    }

    public function getResultReader(): WorkerResultReader
    {
        return $this->resultReader;
    }

    public function getFileCleaner(): WorkerFileCleaner
    {
        return $this->fileCleaner;
    }

    public function getProcessTerminator(): WorkerProcessTerminator
    {
        return $this->processTerminator;
    }
}
