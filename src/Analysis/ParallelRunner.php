<?php

/**
 * Coordinates parallel analysis execution.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use Readalizer\Readalizer\Attributes\Suppress;
use Readalizer\Readalizer\System\MemoryLimitConverter;
use Readalizer\Readalizer\System\ProcessorProfile;

#[Suppress(
    \Readalizer\Readalizer\Rules\MaxClassLengthRule::class,
    \Readalizer\Readalizer\Rules\NoGodClassRule::class,
)]
final class ParallelRunner
{
    private const TOKEN_PREFIX = 'readalizer-token';
    private const EMPTY_STRING = '';
    private const BASELINE_ITEMS_KEY = 'violations';

    private function __construct(
        private readonly string $memoryLimit,
        private readonly FileCollector $fileCollector,
        private readonly FileChunker $fileChunker,
        private readonly JobPlanner $jobPlanner,
        private readonly WorkerProcessFactory $processFactory,
        private readonly WorkerProcessSupervisor $processSupervisor
    ) {}

    public static function create(
        string $readalizerBin,
        string $configPath,
        string $memoryLimit,
        int $workerTimeout
    ): self {
        $processorDetails = ProcessorProfile::create();
        $memoryLimitConverter = MemoryLimitConverter::create();
        $fileCollector = FileCollector::create();
        $fileChunker = FileChunker::create();
        $jobPlanner = JobPlanner::create($processorDetails, $memoryLimitConverter);
        $processFactory = WorkerProcessFactory::create(
            $readalizerBin,
            $configPath,
            $memoryLimit,
            $memoryLimitConverter
        );
        $processSupervisor = self::createProcessSupervisor($workerTimeout);
        return new self($memoryLimit, $fileCollector, $fileChunker, $jobPlanner, $processFactory, $processSupervisor);
    }

    public static function createProcessSupervisor(int $workerTimeout): WorkerProcessSupervisor
    {
        $services = WorkerProcessServiceBundle::create(
            WorkerProgressReporter::create(),
            WorkerResultReader::create(new ViolationPayloadCodec()),
            WorkerFileCleaner::create(),
            WorkerProcessTerminator::create()
        );

        return WorkerProcessSupervisor::create($services, $workerTimeout);
    }

    public function analyse(ParallelRunRequest $request): AnalysisResult
    {
        $targets = $request->getTargets();
        $ignorePaths = iterator_to_array($targets->getIgnore(), false);
        $files = $this->fileCollector->collectFiles($targets->getPaths(), $ignorePaths);

        if ($files->count() === 0) {
            return AnalysisResult::create(RuleViolationCollection::create([]));
        }

        $options = $request->getOptions();
        $jobs = $this->jobPlanner->resolveJobCount($options->getRequestedJobs(), $files->count(), $this->memoryLimit);

        if ($jobs <= 1) {
            return $this->runSequential($request->getRules(), $files, $options, $ignorePaths);
        }

        $violations = $this->runParallel($files, $options, $jobs);
        return AnalysisResult::create($violations);
    }

    #[Suppress(\Readalizer\Readalizer\Rules\NoLongParameterListRule::class)]
    private function createWorkerProcesses(
        FileChunkCollection $chunks,
        int $jobs,
        string $token,
        string $tokenFile,
        ParallelRunConfig $options
    ): WorkerProcessCollection {
        $collection = WorkerProcessCollection::create([]);
        foreach ($chunks as $chunk) {
            $collection->addProcess($this->processFactory->createProcess($chunk, $jobs, $token, $tokenFile, $options));
        }

        return $collection;
    }

    private function prepareProgress(ParallelRunConfig $options, int $totalFiles): void
    {
        $progress = $options->getProgress();
        if ($progress === null) {
            return;
        }

        $progress->setTotal($totalFiles);
        $progress->addSteps(0);
    }

    private function createTokenFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), self::TOKEN_PREFIX);
        if ($path === false) {
            $path = sys_get_temp_dir() . '/' . self::TOKEN_PREFIX . '-' . bin2hex(random_bytes(8));
        }

        file_put_contents($path, bin2hex(random_bytes(16)));
        return $path;
    }

    private function readToken(string $tokenFile): string
    {
        $contents = file_get_contents($tokenFile);
        if (!is_string($contents)) {
            return '';
        }
        return trim($contents);
    }

    /**
     * @param array<int, string> $ignorePaths
     */
    private function runSequential(
        RuleCollection $rules,
        PathCollection $files,
        ParallelRunConfig $options,
        array $ignorePaths
    ): AnalysisResult {
        $analyser = AnalyserFactory::create($rules, $ignorePaths, $options->getCacheConfig());
        return $analyser->analyse(
            $files,
            $options->getProgress(),
            $options->getMaxViolations(),
            $this->buildBaselineFilter($options->getBaselinePath())
        );
    }

    private function runParallel(PathCollection $files, ParallelRunConfig $options, int $jobs): RuleViolationCollection
    {
        $chunks = $this->fileChunker->createChunks($files, $jobs);
        $this->prepareProgress($options, $files->count());

        $tokenFile = $this->createTokenFile();
        $token = $this->readToken($tokenFile);

        $processes = $this->createWorkerProcesses($chunks, $jobs, $token, $tokenFile, $options);
        $violations = $this->processSupervisor->collectViolations(
            $processes,
            $options->getProgress(),
            $options->getMaxViolations()
        );

        @unlink($tokenFile);

        return $violations;
    }

    private function buildBaselineFilter(?string $baselinePath): ?\Closure
    {
        $keys = $this->loadBaselineKeysBestEffort($baselinePath);
        if ($keys === []) {
            return null;
        }

        return static function (RuleViolationCollection $violations) use ($keys): RuleViolationCollection {
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
                if (isset($keys[$key])) {
                    continue;
                }

                $filtered[] = $violation;
            }

            return RuleViolationCollection::create($filtered);
        };
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

}
