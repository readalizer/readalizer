<?php

/**
 * Supervises worker processes and aggregates their results.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use Readalizer\Readalizer\Console\ProgressBar;
use Readalizer\Readalizer\Attributes\Suppress;

#[Suppress(\Readalizer\Readalizer\Rules\NoGodClassRule::class)]
final class WorkerProcessSupervisor
{
    private const SLEEP_MICROSECONDS = 20000;

    private ?WorkerProcessCollection $activeProcesses = null;
    private ?ProgressBar $activeProgress = null;
    private ?RuleViolationCollection $activeViolations = null;

    private function __construct(
        private readonly WorkerProcessServiceBundle $services,
        private readonly int $workerTimeout
    ) {}

    public static function create(WorkerProcessServiceBundle $services, int $workerTimeout): self
    {
        return new self($services, $workerTimeout);
    }

    public function collectViolations(
        WorkerProcessCollection $processes,
        ?ProgressBar $progress,
        int $maxViolations = 0
    ): RuleViolationCollection {
        $this->activeProcesses = $processes;
        $this->activeProgress = $progress;
        $this->activeViolations = RuleViolationCollection::create([]);

        while (!$processes->isEmpty()) {
            $this->handleProcessBatch($maxViolations);

            if ($this->hasReachedMaxViolations($maxViolations)) {
                $this->handleActiveProcessShutdown();
                break;
            }

            usleep(self::SLEEP_MICROSECONDS);
        }

        $this->services->getProgressReporter()->reportCompletion($progress);

        $violations = $this->activeViolations ?? RuleViolationCollection::create([]);
        $this->setActiveStateEmpty();

        return $violations;
    }

    private function handleProcessBatch(int $maxViolations): void
    {
        if ($this->activeProcesses === null) {
            return;
        }

        foreach ($this->activeProcesses as $index => $process) {
            $this->handleProcessEntry($index, $process);
            if ($this->hasReachedMaxViolations($maxViolations)) {
                return;
            }
        }
    }

    private function handleProcessEntry(int $index, WorkerProcess $process): void
    {
        $this->services->getProgressReporter()->updateProgress($process, $this->activeProgress);

        if ($this->hasProcessTimedOut($process)) {
            $this->services->getProcessTerminator()->handleProcessStop($process);
            $this->services->getProgressReporter()->handleProgressClose($process);
            $this->activeProcesses?->removeProcess($index);
            return;
        }

        if ($this->isProcessRunning($process)) {
            return;
        }

        $this->services->getProgressReporter()->updateProgress($process, $this->activeProgress);
        $this->services->getProgressReporter()->handleProgressClose($process);
        $this->activeViolations = $this->mergeViolations($process);
        $this->services->getFileCleaner()->removeFiles($process);
        $this->activeProcesses?->removeProcess($index);
    }

    private function mergeViolations(WorkerProcess $process): RuleViolationCollection
    {
        $current = $this->activeViolations ?? RuleViolationCollection::create([]);
        return $current->merge($this->services->getResultReader()->collectViolations($process));
    }

    private function setActiveStateEmpty(): void
    {
        $this->activeProcesses = null;
        $this->activeProgress = null;
        $this->activeViolations = null;
    }

    private function hasProcessTimedOut(WorkerProcess $process): bool
    {
        return (microtime(true) - $process->getMetrics()->getStartTime()) > $this->workerTimeout;
    }

    private function isProcessRunning(WorkerProcess $process): bool
    {
        $handleContainer = $process->getProcessHandle();
        /** @var resource|null $handle */
        $handle = $handleContainer->resource ?? null;
        if (!is_resource($handle)) {
            return false;
        }
        $status = proc_get_status($handle);
        return $status['running'];
    }

    private function hasReachedMaxViolations(int $maxViolations): bool
    {
        if ($maxViolations <= 0) {
            return false;
        }

        $violations = $this->activeViolations;
        if ($violations === null) {
            return false;
        }

        return $violations->count() >= $maxViolations;
    }

    private function handleActiveProcessShutdown(): void
    {
        if ($this->activeProcesses === null) {
            return;
        }

        foreach ($this->activeProcesses as $index => $process) {
            $this->services->getProcessTerminator()->handleProcessStop($process);
            $this->services->getProgressReporter()->handleProgressClose($process);
            $this->services->getFileCleaner()->removeFiles($process);
            $this->activeProcesses->removeProcess($index);
        }
    }

}
