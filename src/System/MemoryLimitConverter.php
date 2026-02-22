<?php

/**
 * Converts PHP memory limit strings to bytes and back.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\System;

final class MemoryLimitConverter
{
    private const EMPTY_STRING = '';
    private const UNLIMITED = '-1';
    private const ZERO = '0';
    private const UNIT_KILOBYTES = 'K';
    private const UNIT_MEGABYTES = 'M';
    private const UNIT_GIGABYTES = 'G';
    private const UNIT_TERABYTES = 'T';

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function getBytesFromLimit(string $limit): int
    {
        $value = trim($limit);
        if ($value === self::EMPTY_STRING || $value === self::UNLIMITED) {
            return 0;
        }

        $unit = strtoupper(substr($value, -1));
        if (ctype_digit($unit)) {
            return (int) $value;
        }

        $number = (int) substr($value, 0, -1);

        return match ($unit) {
            self::UNIT_KILOBYTES => $number * 1024,
            self::UNIT_MEGABYTES => $number * 1024 * 1024,
            self::UNIT_GIGABYTES => $number * 1024 * 1024 * 1024,
            self::UNIT_TERABYTES => $number * 1024 * 1024 * 1024 * 1024,
            default => (int) $value,
        };
    }

    public function buildLimitFromBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return self::ZERO;
        }

        $megabytes = max(1, intdiv($bytes, 1024 * 1024));
        return $megabytes . self::UNIT_MEGABYTES;
    }
}
