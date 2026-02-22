<?php

/**
 * Represents the configuration for caching.
 *
 * This class encapsulates the settings for enabling/disabling the cache
 * and specifying the cache file path.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Config;

final class CacheConfig
{
    private const ENABLED_FLAG_ON = 1;
    private const ENABLED_FLAG_OFF = 0;

    private function __construct(
        private readonly string $path,
        private readonly int $enabledFlag,
    ) {}

    /**
     * Creates a CacheConfig instance from an array of data.
     *
     * @param array{enabled?: bool, path?: string} $data
     */
    public static function createFromArray(array $data): self
    {
        $enabledFlag = ($data['enabled'] ?? true)
            ? self::ENABLED_FLAG_ON
            : self::ENABLED_FLAG_OFF;

        return new self(
            $data['path'] ?? '.readalizer-cache.json',
            $enabledFlag,
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabledFlag === self::ENABLED_FLAG_ON;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
