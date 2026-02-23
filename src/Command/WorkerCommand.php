<?php

/**
 * Executes internal worker analysis tasks.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

use Readalizer\Readalizer\Attributes\Suppress;
use Readalizer\Readalizer\Analysis\Analyser;
use Readalizer\Readalizer\Analysis\AnalyserFactory;
use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use Readalizer\Readalizer\Analysis\ViolationPayloadCodec;
use Readalizer\Readalizer\Config\Configuration;
use Readalizer\Readalizer\Config\ConfigurationLoader;
use Readalizer\Readalizer\Console\Input;
use Readalizer\Readalizer\Console\Output;

#[Suppress(
    \Readalizer\Readalizer\Rules\MaxClassLengthRule::class,
    \Readalizer\Readalizer\Rules\NoGodClassRule::class,
)]
final class WorkerCommand
{
    private const EMPTY_STRING = '';
    private const BASELINE_ITEMS_KEY = 'violations';
    private const OPTION_FILES = '--worker-files';
    private const OPTION_OUTPUT = '--worker-output';
    private const OPTION_PROGRESS = '--worker-progress';
    private const OPTION_MEMORY = '--memory-limit';
    private const ERROR_UNAUTHORIZED = 'Worker mode is internal only.';
    private const ERROR_MISSING_PATHS = 'Worker files/output missing.';

    private function __construct(
        private readonly Input $input,
        private readonly Output $output,
        private readonly ViolationPayloadCodec $payloadCodec
    ) {}

    public static function create(Input $input, Output $output): self
    {
        return new self($input, $output, new ViolationPayloadCodec());
    }

    public function run(): int
    {
        if (!WorkerAuthorization::create($this->input)->isAuthorized()) {
            $this->output->writeError(self::ERROR_UNAUTHORIZED);
            return 2;
        }

        $config = $this->loadConfiguration();
        $this->applyMemoryLimit($config);

        $paths = $this->resolveWorkerPathSet();
        if ($paths === null) {
            $this->output->writeError(self::ERROR_MISSING_PATHS);
            return 2;
        }

        $analyser = $this->buildAnalyser($config);
        $progressWriter = WorkerProgressWriter::create($paths->getProgressPath());
        $baselineKeys = $this->loadBaselineKeysBestEffort($config->baseline);
        $violations = $this->analysePaths(
            $analyser,
            $paths,
            $progressWriter,
            $config->maxViolations ?? 0,
            $baselineKeys
        );
        $this->writePayload($violations, $paths->getOutputPath());

        return 0;
    }

    private function loadConfiguration(): Configuration
    {
        return ConfigurationLoader::create($this->input)->load();
    }

    private function applyMemoryLimit(Configuration $config): void
    {
        $memoryLimit = $this->input->getOption(self::OPTION_MEMORY) ?? $config->memoryLimit ?? null;
        if (is_string($memoryLimit) && $memoryLimit !== self::EMPTY_STRING) {
            ini_set('memory_limit', $memoryLimit);
        }
    }

    private function resolveWorkerPathSet(): ?WorkerPathSet
    {
        $filesPath = $this->input->getOption(self::OPTION_FILES);
        $outputPath = $this->input->getOption(self::OPTION_OUTPUT);
        $progressPath = $this->input->getOption(self::OPTION_PROGRESS);
        if (
            !is_string($filesPath)
              || $filesPath === self::EMPTY_STRING
              || !is_string($outputPath)
              || $outputPath === self::EMPTY_STRING
        ) {
            return null;
        }

        $paths = $this->loadPathsFromFile($filesPath);
        return WorkerPathSet::create($paths, $outputPath, $progressPath);
    }

    private function loadPathsFromFile(string $path): \Readalizer\Readalizer\Analysis\PathCollection
    {
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return \Readalizer\Readalizer\Analysis\PathCollection::create([]);
        }

        return \Readalizer\Readalizer\Analysis\PathCollection::create($lines);
    }

    private function buildAnalyser(Configuration $config): Analyser
    {
        $rules = RuleCollection::create($config->rules);

        if ($this->input->hasOption('--debug')) {
            return AnalyserFactory::createDebug($rules, $config->ignore, $config->cache);
        }

        return AnalyserFactory::create($rules, $config->ignore, $config->cache);
    }

    /**
     * @param array<string, true> $baselineKeys
     */
    #[Suppress(
        \Readalizer\Readalizer\Rules\NoLongMethodsRule::class,
        \Readalizer\Readalizer\Rules\NoLongParameterListRule::class,
    )]
    private function analysePaths(
        Analyser $analyser,
        WorkerPathSet $paths,
        WorkerProgressWriter $progressWriter,
        int $maxViolations,
        array $baselineKeys
    ): RuleViolationCollection {
        $violations = RuleViolationCollection::create([]);

        foreach ($paths->getPaths() as $file) {
            $analysisResult = $analyser->analyseFile($file);
            $remaining = $this->getRemainingViolations($violations, $maxViolations);
            if ($remaining === 0) {
                break;
            }

            $incoming = $analysisResult->getRuleViolationCollection();
            $incoming = $this->applyBaselineKeys($incoming, $baselineKeys);
            $violations = $remaining > 0
                ? $violations->merge($incoming->getLimitedTo($remaining))
                : $violations->merge($incoming);
            $progressWriter->writeTick();

            if ($maxViolations > 0 && $violations->count() >= $maxViolations) {
                break;
            }
        }

        $progressWriter->handleClose();
        $analyser->saveCache();

        return $violations;
    }

    private function getRemainingViolations(RuleViolationCollection $violations, int $maxViolations): int
    {
        if ($maxViolations <= 0) {
            return -1;
        }

        $remaining = $maxViolations - $violations->count();
        return max(0, $remaining);
    }

    /**
     * @param array<string, true> $baselineKeys
     */
    // @readalizer-suppress NoArrayReturnRule
    private function applyBaselineKeys(
        RuleViolationCollection $violations,
        array $baselineKeys
    ): RuleViolationCollection
    {
        if ($baselineKeys === []) {
            return $violations;
        }

        $filtered = [];
        foreach ($violations as $violation) {
            $key = sha1(
                $violation->getFilePath()
                . "\0"
                . $violation->getLine()
                . "\0"
                . $violation->getMessage()
                . "\0"
                . $violation->getRuleClass()
            );
            if (isset($baselineKeys[$key])) {
                continue;
            }

            $filtered[] = $violation;
        }

        return RuleViolationCollection::create($filtered);
    }

    /**
     * @return array<string, true>
     */
    // @readalizer-suppress NoArrayReturnRule
    #[Suppress(\Readalizer\Readalizer\Rules\NoLongMethodsRule::class)]
    private function loadBaselineKeysBestEffort(?string $baselinePath): array
    {
        if (!is_string($baselinePath) || $baselinePath === self::EMPTY_STRING) {
            return [];
        }

        $contents = @file_get_contents($baselinePath);
        if (!is_string($contents)) {
            return [];
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($contents, true);
        $items = $decoded[self::BASELINE_ITEMS_KEY] ?? null;
        if (!is_array($decoded) || !is_array($items)) {
            return [];
        }

        $keys = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = $this->parseBaselineItem($item);
            if ($normalized === null) {
                continue;
            }

            $keys[
                sha1(
                    $normalized['file']
                    . "\0"
                    . $normalized['line']
                    . "\0"
                    . $normalized['message']
                    . "\0"
                    . $normalized['rule']
                )
            ] = true;
        }

        return $keys;
    }

    /**
     * @param array<mixed, mixed> $item
     * @return array{file: string, line: int, message: string, rule: string}|null
     */
    // @readalizer-suppress NoArrayReturnRule
    private function parseBaselineItem(array $item): ?array
    {
        if (!isset($item['file'], $item['line'], $item['message'], $item['rule'])) {
            return null;
        }

        if (
            !is_string($item['file'])
            || !is_int($item['line'])
            || !is_string($item['message'])
            || !is_string($item['rule'])
        ) {
            return null;
        }

        return [
            'file' => $item['file'],
            'line' => $item['line'],
            'message' => $item['message'],
            'rule' => $item['rule'],
        ];
    }

    private function writePayload(RuleViolationCollection $violations, string $outputPath): void
    {
        $payload = $this->payloadCodec->formatPayload($violations);
        file_put_contents($outputPath, $payload);
    }

}
