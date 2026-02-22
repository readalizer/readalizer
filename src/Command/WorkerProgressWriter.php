<?php

/**
 * Writes per-file progress ticks for worker processes.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Command;

final class WorkerProgressWriter
{
    private const TICK = "1\n";
    private const MODE_APPEND = 'ab';

    private ?\SplFileObject $handle;

    private function __construct(?string $path)
    {
        $this->handle = $this->createHandle($path);
    }

    public static function create(?string $path): self
    {
        return new self($path);
    }

    public function writeTick(): void
    {
        if ($this->handle === null) {
            return;
        }

        $this->handle->fwrite(self::TICK);
        $this->handle->fflush();
    }

    public function handleClose(): void
    {
        $this->handle = null;
    }

    private function createHandle(?string $path): ?\SplFileObject
    {
        if ($path === null) {
            return null;
        }

        try {
            return new \SplFileObject($path, self::MODE_APPEND);
        } catch (\RuntimeException $exception) {
            return null;
        }
    }
}
