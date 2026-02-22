<?php

/**
 * Executes the main analysis command for Readalizer.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

use Readalizer\Readalizer\Analysis\AnalyserFactory;
use Readalizer\Readalizer\Analysis\AnalysisResult;
use Readalizer\Readalizer\Analysis\ParallelRunRequest;
use Readalizer\Readalizer\Analysis\ParallelRunner;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use Readalizer\Readalizer\Console\Input;
use Readalizer\Readalizer\Console\Output;
use Readalizer\Readalizer\Formatter\TextFormatter;

final class AnalyseCommand
{
    private const EXIT_SUCCESS = 0;
    private const EXIT_FAILURE = 1;
    private const EMPTY_STRING = '';

    private function __construct(
        private readonly Input $input,
        private readonly Output $output
    ) {
    }

    public static function create(Input $input, Output $output): self
    {
        return new self($input, $output);
    }

    public function run(): int
    {
        $startedAt = microtime(true);
        $context = $this->createContext();
        if ($context === null) {
            return self::EXIT_FAILURE;
        }

        $analysisResult = $this->runAnalysis($context);
        $this->renderResults($analysisResult->getRuleViolationCollection());
        $this->renderElapsedTime($startedAt);

        return $analysisResult->count() === 0 ? self::EXIT_SUCCESS : self::EXIT_FAILURE;
    }

    private function createContext(): ?AnalyseCommandContext
    {
        $factory = AnalyseCommandContextFactory::create($this->input, $this->output);
        return $factory->createContext();
    }

    private function runAnalysis(AnalyseCommandContext $context): AnalysisResult
    {
        $environment = $context->getEnvironment();
        $this->applyMemoryLimit($environment->getMemoryLimit());

        $options = $context->getOptions();
        if ($options->getRequestedJobs() <= 1) {
            return $this->runSequentialAnalysis($context);
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

    private function runSequentialAnalysis(AnalyseCommandContext $context): AnalysisResult
    {
        $targets = $context->getTargets();
        $analyser = AnalyserFactory::create($context->getRules(), iterator_to_array($targets->getIgnore(), false));
        return $analyser->analyse($targets->getPaths(), $context->getOptions()->getProgress());
    }

    private function applyMemoryLimit(string $limit): void
    {
        if ($limit !== self::EMPTY_STRING) {
            ini_set('memory_limit', $limit);
        }
    }

    private function renderResults(RuleViolationCollection $violations): void
    {
        $formatter = TextFormatter::create();
        $formatter->write($violations, $this->output);
    }

    private function renderElapsedTime(float $startedAt): void
    {
        $elapsed = microtime(true) - $startedAt;
        $seconds = number_format($elapsed, 2);
        $this->output->writeln(sprintf('Time: %ss', $seconds));
    }
}
