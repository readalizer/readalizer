<?php

/**
 * Represents the entire configuration for the Readalizer application.
 *
 * This class consolidates all configurable settings, including rules, ignore
 * paths, memory limits, cache settings, rulesets, baselines, and max violations.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Config;

use Readalizer\Readalizer\Contracts\FileRuleContract;
use Readalizer\Readalizer\Contracts\RuleContract;
use Readalizer\Readalizer\Contracts\RulesetContract;

final class Configuration
{
    /**
     * @param array<int, RuleContract<\PhpParser\Node>|FileRuleContract> $rules
     * @param array<int, string> $ignore
     * @param array<int, string> $paths
     * @param array<int, RulesetContract>|null $ruleset
     */
    private function __construct(
        public readonly array $rules,
        public readonly array $ignore,
        public readonly array $paths,
        public readonly ?string $memoryLimit,
        public readonly ?CacheConfig $cache,
        public readonly ?array $ruleset,
        public readonly ?string $baseline,
        public readonly ?int $maxViolations,
    ) {}

    /**
     * Creates a Configuration instance from an array of data.
     *
     * @param array{
     *     rules?: array<int, RuleContract<\PhpParser\Node>|FileRuleContract>,
     *     ignore?: array<int, string>,
     *     paths?: array<int, string>,
     *     memory_limit?: ?string,
     *     cache?: ?CacheConfig,
     *     ruleset?: array<int, RulesetContract>|null,
     *     baseline?: ?string,
     *     max_violations?: ?int
     * } $data
     */
    public static function create(array $data): self
    {
        return new self(
            $data['rules'] ?? [],
            $data['ignore'] ?? [],
            $data['paths'] ?? [],
            $data['memory_limit'] ?? null,
            $data['cache'] ?? null,
            $data['ruleset'] ?? null,
            $data['baseline'] ?? null,
            $data['max_violations'] ?? null,
        );
    }
}
