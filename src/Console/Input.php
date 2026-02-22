<?php

/**
 * Handles the parsing and access of command-line input arguments and options.
 *
 * This class abstracts away the direct interaction with $_SERVER['argv'],
 * providing a structured way to retrieve arguments by index and options by name.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Console;

final class Input
{
    /** @var array<int, string> */
    private readonly array $args;

    /**
     * @param array<int, string> $argv
     */
    private function __construct(array $argv)
    {
        $this->args = array_slice($argv, 1);
    }

    /**
     * @param array<int, string> $argv
     */
    public static function createFromArgv(array $argv): self
    {
        return new self($argv);
    }

    public function getArgument(int $index): ?string
    {
        return $this->args[$index] ?? null;
    }

    // @readalizer-suppress NoArrayReturnRule
    /** @return array<int, string> */
    public function getArguments(): array
    {
        return $this->args;
    }

    public function hasOption(string $name): bool
    {
        foreach ($this->args as $arg) {
            if ($arg === $name || str_starts_with($arg, $name . '=')) {
                return true;
            }
        }
        return false;
    }

    public function getOption(string $name): ?string
    {
        $nameWithEquals = $name . '=';
        foreach ($this->args as $i => $arg) {
            if ($arg === $name) {
                return $this->args[$i + 1] ?? null;
            }
            if (str_starts_with($arg, $nameWithEquals)) {
                return substr($arg, strlen($nameWithEquals));
            }
        }

        return null;
    }
}
