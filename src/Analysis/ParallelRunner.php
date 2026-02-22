<?php

/**
 * Coordinates parallel analysis execution.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use Millerphp\Readalizer\System\MemoryLimitConverter;
use Millerphp\Readalizer\System\ProcessorProfile;

final class ParallelRunner
{
    private const TOKEN_PREFIX = 'readalizer-token';

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

    private function createWorkerProcesses(
        FileChunkCollection $chunks,
        int $jobs,
        string $token,
        string $tokenFile
    ): WorkerProcessCollection {
        $collection = WorkerProcessCollection::create([]);
        foreach ($chunks as $chunk) {
            $collection->addProcess($this->processFactory->createProcess($chunk, $jobs, $token, $tokenFile));
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
        $analyser = AnalyserFactory::create($rules, $ignorePaths);
        return $analyser->analyse($files, $options->getProgress());
    }

    private function runParallel(PathCollection $files, ParallelRunConfig $options, int $jobs): RuleViolationCollection
    {
        $chunks = $this->fileChunker->createChunks($files, $jobs);
        $this->prepareProgress($options, $files->count());

        $tokenFile = $this->createTokenFile();
        $token = $this->readToken($tokenFile);

        $processes = $this->createWorkerProcesses($chunks, $jobs, $token, $tokenFile);
        $violations = $this->processSupervisor->collectViolations($processes, $options->getProgress());

        @unlink($tokenFile);

        return $violations;
    }

}
