<?php

/**
 * Configures parallel execution options.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use Millerphp\Readalizer\Console\ProgressBar;

final class ParallelRunConfig
{
    private function __construct(
        private readonly int $requestedJobs,
        private readonly ?ProgressBar $progress
    ) {
    }

    public static function create(int $requestedJobs, ?ProgressBar $progress): self
    {
        return new self($requestedJobs, $progress);
    }

    public function getRequestedJobs(): int
    {
        return $this->requestedJobs;
    }

    public function getProgress(): ?ProgressBar
    {
        return $this->progress;
    }
}
