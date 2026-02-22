<?php

/**
 * Manages all command-line interface output.
 *
 * This class provides standardized methods for writing messages to
 * standard output (stdout) and standard error (stderr), abstracting
 * away direct echo or fwrite calls.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Console;

final class Output
{
    private const EOL = "\n";

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function writeln(string $message): void
    {
        fwrite(STDOUT, $message . self::EOL);
    }

    public function writeError(string $message): void
    {
        fwrite(STDERR, $message . self::EOL);
    }
}
