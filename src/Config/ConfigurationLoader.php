<?php

/**
 * Loads and parses the application's configuration from a PHP file.
 *
 * This class is responsible for locating the configuration file,
 * reading its contents, and translating them into a structured
 * Configuration object, handling default values and CLI overrides.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Config;

use Readalizer\Readalizer\Console\Input;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use Readalizer\Readalizer\Contracts\RuleContract;
use Readalizer\Readalizer\Contracts\RulesetContract;

final class ConfigurationLoader
{
    private function __construct(private readonly Input $input)
    {
    }

    public static function create(Input $input): self
    {
        return new self($input);
    }

    public function load(): Configuration
    {
        $configPath = $this->resolveConfigPath();

        if ($configPath === null) {
            return Configuration::create([]);
        }

        $config = $this->loadConfigArray($configPath);
        $cacheConfig = $this->buildCacheConfig($config);
        $rules = $this->buildRules($config);

        return Configuration::create(
            $this->buildConfigurationData($config, $rules, $cacheConfig)
        );
    }

    private function resolveConfigPath(): ?string
    {
        $explicit = $this->input->getOption('--config');
        if ($explicit !== null) {
            return file_exists($explicit) ? $explicit : null;
        }

        return file_exists('readalizer.php') ? 'readalizer.php' : null;
    }

    /** @return array<string, mixed> */
    // @readalizer-suppress NoArrayReturnRule
    private function loadConfigArray(string $configPath): array
    {
        $config = require $configPath;

        if (!is_array($config)) {
            return [];
        }

        /** @var array<string, mixed> $config */
        return $config;
    }

    /** @param array<string, mixed> $config */
    private function buildCacheConfig(array $config): ?CacheConfig
    {
        if (!isset($config['cache'])) {
            return null;
        }

        if (!is_array($config['cache'])) {
            return null;
        }

        /** @var array{enabled?: bool, path?: string} $cache */
        $cache = $config['cache'];

        return CacheConfig::createFromArray($cache);
    }

    /**
     * @param array<string, mixed> $config
     * @return array<int, RuleContract<\PhpParser\Node>|FileRuleContract>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function buildRules(array $config): array
    {
        $rules = isset($config['rules']) && is_array($config['rules'])
            ? array_values(array_filter(
                $config['rules'],
                static fn(mixed $rule): bool => $rule instanceof RuleContract || $rule instanceof FileRuleContract
            ))
            : [];
        /** @var array<int, RuleContract<\PhpParser\Node>|FileRuleContract> $rules */
        if (!isset($config['ruleset'])) {
            return $rules;
        }

        if (!is_array($config['ruleset'])) {
            return $rules;
        }

        $expander = RulesetExpander::create();
        $rulesets = array_values(array_filter(
            $config['ruleset'],
            static fn(mixed $ruleset): bool => $ruleset instanceof RulesetContract
        ));
        $expanded = $expander->buildRules($rulesets)->getIterator();
        $expandedRules = array_values(iterator_to_array($expanded));
        /** @var array<int, RuleContract<\PhpParser\Node>|FileRuleContract> $expandedRules */

        return array_merge($rules, $expandedRules);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, RuleContract<\PhpParser\Node>|FileRuleContract> $rules
     * @return array{
     *   rules: array<int, RuleContract<\PhpParser\Node>|FileRuleContract>,
     *   ignore: array<int, string>,
     *   paths: array<int, string>,
     *   memory_limit: string|null,
     *   cache: CacheConfig|null,
     *   ruleset: array<int, RulesetContract>|null,
     *   baseline: string|null,
     *   max_violations: int|null
     * }
     */
    // @readalizer-suppress NoArrayReturnRule
    private function buildConfigurationData(array $config, array $rules, ?CacheConfig $cacheConfig): array
    {
        $ignore = isset($config['ignore']) && is_array($config['ignore'])
            ? array_values(array_filter($config['ignore'], 'is_string'))
            : [];
        $paths = isset($config['paths']) && is_array($config['paths'])
            ? array_values(array_filter($config['paths'], 'is_string'))
            : [];
        $memoryLimit = is_string($config['memory_limit'] ?? null) ? $config['memory_limit'] : null;
        $baseline = is_string($config['baseline'] ?? null) ? $config['baseline'] : null;
        $maxViolations = is_int($config['max_violations'] ?? null) ? $config['max_violations'] : null;

        $ruleset = $this->buildRuleset($config);

        return [
            'rules' => $rules,
            'ignore' => $ignore,
            'paths' => $paths,
            'memory_limit' => $memoryLimit,
            'cache' => $cacheConfig,
            'ruleset' => $ruleset,
            'baseline' => $baseline,
            'max_violations' => $maxViolations,
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @return array<int, RulesetContract>|null
     */
    // @readalizer-suppress NoArrayReturnRule
    private function buildRuleset(array $config): ?array
    {
        if (!isset($config['ruleset']) || !is_array($config['ruleset'])) {
            return null;
        }

        return array_values(array_filter(
            $config['ruleset'],
            static fn(mixed $ruleset): bool => $ruleset instanceof RulesetContract
        ));
    }
}
