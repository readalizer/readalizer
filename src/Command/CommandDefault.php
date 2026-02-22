<?php

/**
 * Provides default values for command options.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

final class CommandDefault
{
    public const MEMORY_LIMIT = '2G';
    public const WORKER_TIMEOUT = 120;
    public const READALIZER_BIN = 'bin/readalizer';
    public const CONFIG_PATH = 'readalizer.php';

    private function __construct()
    {
    }
}
