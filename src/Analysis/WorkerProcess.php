<?php

/**
 * Represents a spawned worker process and its metadata.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class WorkerProcess
{
    private function __construct(
        private readonly \stdClass $processHandle,
        private readonly WorkerProcessPathSet $paths,
        private readonly WorkerProcessRuntime $metrics,
        private readonly WorkerProcessState $state
    ) {
    }

    public static function create(
        \stdClass $processHandle,
        WorkerProcessPathSet $paths,
        WorkerProcessRuntime $metrics,
        WorkerProcessState $state
    ): self {
        return new self($processHandle, $paths, $metrics, $state);
    }

    public function getProcessHandle(): \stdClass
    {
        return $this->processHandle;
    }

    public function getPaths(): WorkerProcessPathSet
    {
        return $this->paths;
    }

    public function getMetrics(): WorkerProcessRuntime
    {
        return $this->metrics;
    }

    public function getState(): WorkerProcessState
    {
        return $this->state;
    }
}
