<?php

/**
 * Represents file locations for worker execution.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Command;

use Millerphp\Readalizer\Analysis\PathCollection;

final class WorkerPathSet
{
    private function __construct(
        private readonly PathCollection $paths,
        private readonly string $outputPath,
        private readonly ?string $progressPath
    ) {
    }

    public static function create(PathCollection $paths, string $outputPath, ?string $progressPath): self
    {
        return new self($paths, $outputPath, $progressPath);
    }

    public function getPaths(): PathCollection
    {
        return $this->paths;
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function getProgressPath(): ?string
    {
        return $this->progressPath;
    }
}
