<?php

/**
 * Executes internal worker analysis tasks.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

use Readalizer\Readalizer\Analysis\Analyser;
use Readalizer\Readalizer\Analysis\AnalyserFactory;
use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use Readalizer\Readalizer\Analysis\ViolationPayloadCodec;
use Readalizer\Readalizer\Config\Configuration;
use Readalizer\Readalizer\Config\ConfigurationLoader;
use Readalizer\Readalizer\Console\Input;
use Readalizer\Readalizer\Console\Output;

final class WorkerCommand
{
    private const EMPTY_STRING = '';
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
        $violations = $this->analysePaths($analyser, $paths, $progressWriter);
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
            return AnalyserFactory::createDebug($rules, $config->ignore);
        }

        return AnalyserFactory::create($rules, $config->ignore);
    }

    private function analysePaths(
        Analyser $analyser,
        WorkerPathSet $paths,
        WorkerProgressWriter $progressWriter
    ): RuleViolationCollection {
        $violations = RuleViolationCollection::create([]);

        foreach ($paths->getPaths() as $file) {
            $analysisResult = $analyser->analyseFile($file);
            $violations = $violations->merge($analysisResult->getRuleViolationCollection());
            $progressWriter->writeTick();
        }

        $progressWriter->handleClose();

        return $violations;
    }

    private function writePayload(RuleViolationCollection $violations, string $outputPath): void
    {
        $payload = $this->payloadCodec->formatPayload($violations);
        file_put_contents($outputPath, $payload);
    }

}
