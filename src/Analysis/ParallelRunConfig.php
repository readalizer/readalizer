<?php

/**
 * Configures parallel execution options.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use Readalizer\Readalizer\Console\ProgressBar;
use Readalizer\Readalizer\Config\CacheConfig;
use Readalizer\Readalizer\Attributes\Suppress;

#[Suppress(
    \Readalizer\Readalizer\Rules\NoGodClassRule::class,
    \Readalizer\Readalizer\Rules\SingleResponsibilityClassRule::class,
    \Readalizer\Readalizer\Rules\NoStaticMethodsRule::class,
)]
final class ParallelRunConfig
{
    private const CACHE_OVERRIDE_NONE = 0;
    private const CACHE_OVERRIDE_ENABLE = 1;
    private const CACHE_OVERRIDE_DISABLE = 2;

    #[Suppress(\Readalizer\Readalizer\Rules\NoConstructorWorkRule::class)]
    private function __construct(
        private readonly int $requestedJobs,
        private readonly ?ProgressBar $progress,
        private readonly string $outputFormat,
        private readonly ?string $outputPath,
        private readonly ?string $baselinePath,
        private readonly ?string $generateBaselinePath,
        private readonly int $maxViolations,
        private readonly ?CacheConfig $cacheConfig,
        private readonly int $cacheCliOverride
    ) {
    }

    #[Suppress(\Readalizer\Readalizer\Rules\NoLongParameterListRule::class)]
    public static function create(
        int $requestedJobs,
        ?ProgressBar $progress,
        string $outputFormat = 'text',
        ?string $outputPath = null,
        ?string $baselinePath = null,
        ?string $generateBaselinePath = null,
        int $maxViolations = 5000,
        ?CacheConfig $cacheConfig = null,
        ?bool $cacheCliOverride = null
    ): self {
        return new self(
            $requestedJobs,
            $progress,
            $outputFormat,
            $outputPath,
            $baselinePath,
            $generateBaselinePath,
            $maxViolations,
            $cacheConfig,
            self::mapCacheCliOverride($cacheCliOverride)
        );
    }

    public function getRequestedJobs(): int
    {
        return $this->requestedJobs;
    }

    public function getProgress(): ?ProgressBar
    {
        return $this->progress;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function getOutputPath(): ?string
    {
        return $this->outputPath;
    }

    public function getBaselinePath(): ?string
    {
        return $this->baselinePath;
    }

    public function getGenerateBaselinePath(): ?string
    {
        return $this->generateBaselinePath;
    }

    public function getMaxViolations(): int
    {
        return $this->maxViolations;
    }

    public function getCacheConfig(): ?CacheConfig
    {
        return $this->cacheConfig;
    }

    public function hasCacheCliEnable(): bool
    {
        return $this->cacheCliOverride === self::CACHE_OVERRIDE_ENABLE;
    }

    public function hasCacheCliDisable(): bool
    {
        return $this->cacheCliOverride === self::CACHE_OVERRIDE_DISABLE;
    }

    private static function mapCacheCliOverride(?bool $cacheCliOverride): int
    {
        if ($cacheCliOverride === true) {
            return self::CACHE_OVERRIDE_ENABLE;
        }

        if ($cacheCliOverride === false) {
            return self::CACHE_OVERRIDE_DISABLE;
        }

        return self::CACHE_OVERRIDE_NONE;
    }
}
