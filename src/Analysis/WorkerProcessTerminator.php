<?php

/**
 * Terminates worker processes that exceed time limits.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class WorkerProcessTerminator
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function handleProcessStop(WorkerProcess $process): void
    {
        $handleContainer = $process->getProcessHandle();
        /** @var resource|null $handle */
        $handle = $handleContainer->resource ?? null;
        if (!is_resource($handle)) {
            return;
        }

        proc_terminate($handle);
        proc_close($handle);
    }
}
