<?php

/**
 * Stores worker file path metadata.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class WorkerProcessPathSet
{
    private function __construct(
        private readonly string $filesPath,
        private readonly string $outputPath,
        private readonly string $progressPath
    ) {
    }

    public static function create(string $filesPath, string $outputPath, string $progressPath): self
    {
        return new self($filesPath, $outputPath, $progressPath);
    }

    public function getFilesPath(): string
    {
        return $this->filesPath;
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function getProgressPath(): string
    {
        return $this->progressPath;
    }
}
