<?php

/**
 * Runtime settings for parallel execution.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

final class ParallelRunEnvironment
{
    private function __construct(
        private readonly string $memoryLimit,
        private readonly string $configPath,
        private readonly string $readalizerBin,
        private readonly int $workerTimeout
    ) {
    }

    public static function create(
        string $memoryLimit,
        string $configPath,
        string $readalizerBin,
        int $workerTimeout
    ): self {
        return new self($memoryLimit, $configPath, $readalizerBin, $workerTimeout);
    }

    public function getMemoryLimit(): string
    {
        return $this->memoryLimit;
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    public function getReadalizerBin(): string
    {
        return $this->readalizerBin;
    }

    public function getWorkerTimeout(): int
    {
        return $this->workerTimeout;
    }
}
