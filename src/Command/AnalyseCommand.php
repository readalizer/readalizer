<?php

/**
 * Executes the main analysis command for Readalizer.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

use Readalizer\Readalizer\Attributes\Suppress;
use Readalizer\Readalizer\Analysis\AnalyserFactory;
use Readalizer\Readalizer\Analysis\AnalysisResult;
use Readalizer\Readalizer\Analysis\ParallelRunRequest;
use Readalizer\Readalizer\Analysis\ParallelRunner;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Console\Input;
use Readalizer\Readalizer\Console\Output;
use Readalizer\Readalizer\Formatter\JsonFormatter;
use Readalizer\Readalizer\Formatter\TextFormatter;

#[Suppress(
    \Readalizer\Readalizer\Rules\MaxClassLengthRule::class,
    \Readalizer\Readalizer\Rules\NoGodClassRule::class,
)]
final class AnalyseCommand
{
    private const EXIT_SUCCESS = 0;
    private const EXIT_FAILURE = 1;
    private const EMPTY_STRING = '';
    private const OPTION_BASELINE = '--baseline';
    private const FORMAT_JSON = 'json';
    private const ERROR_OUTPUT_WRITE = 'Error: failed to write output file: %s';
    private const ERROR_BASELINE_WRITE = 'Error: failed to write baseline file: %s';
    private const ERROR_BASELINE_READ = 'Error: failed to read baseline file: %s';
    private const ERROR_BASELINE_INVALID_JSON = 'Error: invalid baseline JSON: %s';
    private const ERROR_BASELINE_MISSING_KEY = 'Error: baseline file missing "%s" array: %s';
    private const MAX_VIOLATIONS_NOTICE = 'Max violations limit reached (%d). Analysis stopped.';
    private const BASELINE_KEY = 'violations';

    private function __construct(
        private readonly Input $input,
        private readonly Output $output
    ) {
    }

    public static function create(Input $input, Output $output): self
    {
        return new self($input, $output);
    }

    #[Suppress(
        \Readalizer\Readalizer\Rules\NoLongMethodsRule::class,
        \Readalizer\Readalizer\Rules\MaxMethodStatementsRule::class,
    )]
    public function run(): int
    {
        $startedAt = microtime(true);
        $context = $this->createContext();
        if ($context === null) {
            return self::EXIT_FAILURE;
        }

        $options = $context->getOptions();
        $baselineKeys = $this->resolveBaselineKeys($options->getBaselinePath());
        if ($baselineKeys === null) {
            return self::EXIT_FAILURE;
        }

        $analysisResult = $this->runAnalysis($context, $baselineKeys);
        $rawViolations = $analysisResult->getRuleViolationCollection();

        try {
            $this->writeBaselineIfRequested($rawViolations, $options->getGenerateBaselinePath());
        } catch (\RuntimeException $e) {
            $this->output->writeError($e->getMessage());
            return self::EXIT_FAILURE;
        }

        $violations = $this->applyBaselineKeys($rawViolations, $baselineKeys);

        $violations = $this->applyMaxViolations($violations, $options->getMaxViolations());

        try {
            $this->renderResults($violations, $options->getOutputFormat(), $options->getOutputPath());
        } catch (\RuntimeException $e) {
            $this->output->writeError($e->getMessage());
            return self::EXIT_FAILURE;
        }
        $this->renderElapsedTime($startedAt, $options->getOutputFormat(), $options->getOutputPath());

        return $violations->count() === 0 ? self::EXIT_SUCCESS : self::EXIT_FAILURE;
    }

    private function createContext(): ?AnalyseCommandContext
    {
        $factory = AnalyseCommandContextFactory::create($this->input, $this->output);
        return $factory->createContext();
    }

    /**
     * @param array<string, true> $baselineKeys
     */
    // @readalizer-suppress NoArrayReturnRule
    private function runAnalysis(AnalyseCommandContext $context, array $baselineKeys): AnalysisResult
    {
        $environment = $context->getEnvironment();
        $this->applyMemoryLimit($environment->getMemoryLimit());

        $options = $context->getOptions();
        if ($options->getRequestedJobs() <= 1) {
            return $this->runSequentialAnalysis($context, $baselineKeys);
        }

        $runner = ParallelRunner::create(
            $environment->getReadalizerBin(),
            $environment->getConfigPath(),
            $environment->getMemoryLimit(),
            $environment->getWorkerTimeout()
        );

        $request = ParallelRunRequest::create(
            $context->getRules(),
            $context->getTargets(),
            $context->getOptions()
        );

        return $runner->analyse($request);
    }

    /**
     * @param array<string, true> $baselineKeys
     */
    // @readalizer-suppress NoArrayReturnRule
    private function runSequentialAnalysis(AnalyseCommandContext $context, array $baselineKeys): AnalysisResult
    {
        $targets = $context->getTargets();
        $analyser = AnalyserFactory::create(
            $context->getRules(),
            iterator_to_array($targets->getIgnore(), false),
            $context->getOptions()->getCacheConfig()
        );
        $filter = $this->buildBaselineFilter($baselineKeys);
        return $analyser->analyse(
            $targets->getPaths(),
            $context->getOptions()->getProgress(),
            $context->getOptions()->getMaxViolations(),
            $filter
        );
    }

    private function applyMemoryLimit(string $limit): void
    {
        if ($limit !== self::EMPTY_STRING) {
            ini_set('memory_limit', $limit);
        }
    }

    private function renderResults(RuleViolationCollection $violations, string $format, ?string $outputPath): void
    {
        $payload = $this->formatResults($violations, $format, $outputPath);
        if ($outputPath !== null && $outputPath !== self::EMPTY_STRING) {
            $written = @file_put_contents($outputPath, $payload);
            if ($written === false) {
                throw new \RuntimeException(sprintf(self::ERROR_OUTPUT_WRITE, $outputPath));
            }

            return;
        }

        fwrite(STDOUT, $payload);
    }

    private function formatResults(RuleViolationCollection $violations, string $format, ?string $outputPath): string
    {
        if ($format === self::FORMAT_JSON) {
            $formatter = new JsonFormatter();
            return $formatter->format($violations);
        }

        $formatter = TextFormatter::create($outputPath === null);
        return $formatter->format($violations);
    }

    private function renderElapsedTime(float $startedAt, string $format, ?string $outputPath): void
    {
        $elapsed = microtime(true) - $startedAt;
        $seconds = number_format($elapsed, 2);
        $message = sprintf('Time: %ss', $seconds);

        $writesToStderr = $format === self::FORMAT_JSON;
        $hasOutputFile = $outputPath !== null && $outputPath !== self::EMPTY_STRING;
        if ($writesToStderr || $hasOutputFile) {
            $this->output->writeError($message);
            return;
        }

        $this->output->writeln($message);
    }

    private function writeBaselineIfRequested(RuleViolationCollection $violations, ?string $path): void
    {
        if ($path === null || $path === self::EMPTY_STRING) {
            return;
        }

        $formatter = new JsonFormatter();
        $written = @file_put_contents($path, $formatter->format($violations));
        if ($written !== false) {
            return;
        }

        throw new \RuntimeException(sprintf(self::ERROR_BASELINE_WRITE, $path));
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
            if (isset($baselineKeys[$this->buildViolationKey($violation)])) {
                continue;
            }

            $filtered[] = $violation;
        }

        return RuleViolationCollection::create($filtered);
    }

    /**
     * @return array<string, true>|null
     */
    // @readalizer-suppress NoArrayReturnRule
    private function resolveBaselineKeys(?string $baselinePath): ?array
    {
        if ($baselinePath === null || $baselinePath === self::EMPTY_STRING) {
            return [];
        }

        return $this->loadBaselineKeys($baselinePath);
    }

    /**
     * @param array<string, true> $baselineKeys
     */
    // @readalizer-suppress NoArrayReturnRule
    private function buildBaselineFilter(array $baselineKeys): ?\Closure
    {
        if ($baselineKeys === []) {
            return null;
        }

        return function (RuleViolationCollection $violations) use ($baselineKeys): RuleViolationCollection {
            return $this->applyBaselineKeys($violations, $baselineKeys);
        };
    }

    private function applyMaxViolations(
        RuleViolationCollection $violations,
        int $maxViolations
    ): RuleViolationCollection
    {
        if ($maxViolations <= 0 || $violations->count() < $maxViolations) {
            return $violations;
        }

        $this->output->writeError(sprintf(self::MAX_VIOLATIONS_NOTICE, $maxViolations));

        return $violations->getLimitedTo($maxViolations);
    }

    /**
     * @return array<string, true>|null
     */
    // @readalizer-suppress NoArrayReturnRule
    #[Suppress(\Readalizer\Readalizer\Rules\NoLongMethodsRule::class)]
    private function loadBaselineKeys(string $path): ?array
    {
        $contents = @file_get_contents($path);
        if (!is_string($contents)) {
            if (!$this->input->hasOption(self::OPTION_BASELINE) && !file_exists($path)) {
                return [];
            }

            $this->output->writeError(sprintf(self::ERROR_BASELINE_READ, $path));
            return null;
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            $this->output->writeError(sprintf(self::ERROR_BASELINE_INVALID_JSON, $path));
            return null;
        }

        $items = $decoded[self::BASELINE_KEY] ?? null;
        if (!is_array($items)) {
            $this->output->writeError(sprintf(self::ERROR_BASELINE_MISSING_KEY, self::BASELINE_KEY, $path));
            return null;
        }

        $keys = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $file = $item['file'] ?? null;
            $line = $item['line'] ?? null;
            $message = $item['message'] ?? null;
            $rule = $item['rule'] ?? null;
            if (!is_string($file) || !is_int($line) || !is_string($message) || !is_string($rule)) {
                continue;
            }

            $keys[$this->buildViolationKeyFromParts($file, $line, $message, $rule)] = true;
        }

        return $keys;
    }

    private function buildViolationKey(RuleViolation $violation): string
    {
        return $this->buildViolationKeyFromParts(
            $violation->getFilePath(),
            $violation->getLine(),
            $violation->getMessage(),
            $violation->getRuleClass()
        );
    }

    private function buildViolationKeyFromParts(string $file, int $line, string $message, string $rule): string
    {
        return sha1($file . "\0" . $line . "\0" . $message . "\0" . $rule);
    }
}
