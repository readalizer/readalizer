<?php

/**
 * Determines the worker count for parallel analysis.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use Millerphp\Readalizer\System\ProcessorProfile;
use Millerphp\Readalizer\System\MemoryLimitConverter;

final class JobPlanner
{
    private const MIN_WORKER_MEMORY = 134217728; // 128M

    private function __construct(
        private readonly ProcessorProfile $processorDetails,
        private readonly MemoryLimitConverter $memoryLimitConverter
    ) {
    }

    public static function create(ProcessorProfile $processorDetails, MemoryLimitConverter $memoryLimitConverter): self
    {
        return new self($processorDetails, $memoryLimitConverter);
    }

    public function resolveJobCount(int $requestedJobs, int $fileCount, string $memoryLimit): int
    {
        $cores = $this->processorDetails->getLogicalCores();
        $cpuCap = max(1, intdiv($cores, 2));
        $jobs = max(1, min($requestedJobs, $cpuCap, $fileCount));

        $bytes = $this->memoryLimitConverter->getBytesFromLimit($memoryLimit);
        if ($bytes > 0) {
            $maxByMemory = max(1, intdiv($bytes, self::MIN_WORKER_MEMORY));
            $jobs = min($jobs, $maxByMemory);
        }

        return $jobs;
    }
}
