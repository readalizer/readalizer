<?php

/**
 * Reads processor information from the current host.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\System;

final class ProcessorProfile
{
    private const ENV_PROCESSORS = 'NUMBER_OF_PROCESSORS';
    private const PROC_CPUINFO = '/proc/cpuinfo';
    private const PROC_PATTERN = '/^processor\\s*:\\s*\\d+/m';
    private const CORES_COMMAND = 'getconf _NPROCESSORS_ONLN 2>/dev/null';
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function getLogicalCores(): int
    {
        $cores = $this->readCoresFromEnvironment();
        if ($cores !== null) {
            return $cores;
        }

        $cores = $this->readCoresFromProc();
        if ($cores !== null) {
            return $cores;
        }

        $cores = $this->readCoresFromCommand();
        if ($cores !== null) {
            return $cores;
        }

        return 1;
    }

    private function readCoresFromEnvironment(): ?int
    {
        $env = getenv(self::ENV_PROCESSORS);
        if (is_string($env) && ctype_digit($env)) {
            return max(1, (int) $env);
        }

        return null;
    }

    private function readCoresFromProc(): ?int
    {
        $path = self::PROC_CPUINFO;
        if (!is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $count = preg_match_all(self::PROC_PATTERN, $contents);
        return $count > 0 ? $count : null;
    }

    private function readCoresFromCommand(): ?int
    {
        if (!function_exists('shell_exec')) {
            return null;
        }

        $output = shell_exec(self::CORES_COMMAND);
        if (!is_string($output)) {
            return null;
        }

        $value = trim($output);
        return ctype_digit($value) ? max(1, (int) $value) : null;
    }
}
