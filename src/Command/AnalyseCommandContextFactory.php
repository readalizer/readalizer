<?php

/**
 * Builds analysis run context from CLI input and configuration.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Command;

use Millerphp\Readalizer\Analysis\AnalysisScope;
use Millerphp\Readalizer\Analysis\ParallelRunConfig;
use Millerphp\Readalizer\Analysis\PathCollection;
use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Config\Configuration;
use Millerphp\Readalizer\Config\ConfigurationLoader;
use Millerphp\Readalizer\Console\Input;
use Millerphp\Readalizer\Console\Output;

final class AnalyseCommandContextFactory
{
    private const OPTION_JOBS = '--jobs';
    private const OPTION_WORKER_TIMEOUT = '--worker-timeout';
    private const OPTION_MEMORY = '--memory-limit';
    private const OPTION_CONFIG = '--config';
    private const EMPTY_STRING = '';
    private const ERROR_NO_PATHS = "Error: no paths specified. Pass paths as arguments or set 'paths' in your config.";

    private function __construct(
        private readonly Input $input,
        private readonly Output $output
    ) {
    }

    public static function create(Input $input, Output $output): self
    {
        return new self($input, $output);
    }

    public function createContext(): ?AnalyseCommandContext
    {
        $config = $this->loadConfiguration();
        $paths = $this->resolvePaths($config);
        if ($paths === null) {
            return null;
        }

        $rules = RuleCollection::create($config->rules);
        $targets = AnalysisScope::create($paths, $config->ignore);
        $progress = ProgressBarFactory::create($this->input)->build($paths);
        $options = ParallelRunConfig::create($this->resolveJobs(), $progress);
        $environment = ParallelRunEnvironment::create(
            $this->resolveMemoryLimit($config),
            $this->resolveConfigPath(),
            $this->resolveReadalizerBinary(),
            $this->resolveWorkerTimeout()
        );

        return AnalyseCommandContext::create($rules, $targets, $options, $environment);
    }

    private function loadConfiguration(): Configuration
    {
        return ConfigurationLoader::create($this->input)->load();
    }

    private function resolvePaths(Configuration $config): ?PathCollection
    {
        $cliPaths = array_filter(
            $this->input->getArguments(),
            fn(string $arg) => !str_starts_with($arg, '-')
        );
        $paths = $cliPaths === [] ? $config->paths : array_values($cliPaths);
        $collection = PathCollection::create($paths);

        if ($collection->count() === 0) {
            $this->output->writeError(self::ERROR_NO_PATHS);
            return null;
        }

        return $collection;
    }

    private function resolveJobs(): int
    {
        $value = $this->input->getOption(self::OPTION_JOBS);
        if ($value === null) {
            return 1;
        }

        $jobs = (int) $value;
        return $jobs > 0 ? $jobs : 1;
    }

    private function resolveWorkerTimeout(): int
    {
        $value = $this->input->getOption(self::OPTION_WORKER_TIMEOUT);
        if ($value === null) {
            return CommandDefault::WORKER_TIMEOUT;
        }

        $timeout = (int) $value;
        return $timeout > 0 ? $timeout : CommandDefault::WORKER_TIMEOUT;
    }

    private function resolveMemoryLimit(Configuration $config): string
    {
        $cli = $this->input->getOption(self::OPTION_MEMORY);
        if (is_string($cli) && $cli !== self::EMPTY_STRING) {
            return $cli;
        }

        if (is_string($config->memoryLimit) && $config->memoryLimit !== self::EMPTY_STRING) {
            return $config->memoryLimit;
        }

        return CommandDefault::MEMORY_LIMIT;
    }

    private function resolveConfigPath(): string
    {
        $value = $this->input->getOption(self::OPTION_CONFIG);
        return is_string($value) && $value !== self::EMPTY_STRING ? $value : CommandDefault::CONFIG_PATH;
    }

    private function resolveReadalizerBinary(): string
    {
        $argv = $_SERVER['argv'] ?? [];
        if (is_array($argv) && isset($argv[0]) && is_string($argv[0])) {
            return $argv[0];
        }

        return CommandDefault::READALIZER_BIN;
    }
}
