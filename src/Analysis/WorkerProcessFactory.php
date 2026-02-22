<?php

/**
 * Spawns worker processes for parallel analysis.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use Millerphp\Readalizer\System\MemoryLimitConverter;

final class WorkerProcessFactory
{
    private const INTERNAL_ENV = 'READALIZER_INTERNAL';
    private const INTERNAL_ENV_VALUE = '1';
    private const FILE_PREFIX = 'readalizer-worker-files';
    private const OUTPUT_PREFIX = 'readalizer-worker-out';
    private const PROGRESS_PREFIX = 'readalizer-worker-progress';
    private const NEWLINE = "\n";
    private const DEV_NULL = '/dev/null';

    private function __construct(
        private readonly string $readalizerBin,
        private readonly string $configPath,
        private readonly string $memoryLimit,
        private readonly MemoryLimitConverter $memoryLimitConverter
    ) {
    }

    public static function create(
        string $readalizerBin,
        string $configPath,
        string $memoryLimit,
        MemoryLimitConverter $memoryLimitConverter
    ): self {
        return new self($readalizerBin, $configPath, $memoryLimit, $memoryLimitConverter);
    }

    public function createProcess(PathCollection $files, int $jobs, string $token, string $tokenFile): WorkerProcess
    {
        $filesPath = $this->writeFileList($files);
        $outputPath = $this->createTempPath(self::OUTPUT_PREFIX);
        $progressPath = $this->createTempPath(self::PROGRESS_PREFIX);
        $memoryPerWorker = $this->buildMemoryLimitPerWorker($jobs);
        $paths = WorkerProcessPathSet::create($filesPath, $outputPath, $progressPath);
        $security = WorkerCommandSecurity::create($token, $tokenFile);

        $command = $this->buildCommand($paths, $security, $memoryPerWorker);
        $processHandle = $this->createProcessHandle($command);
        $metrics = WorkerProcessRuntime::create(microtime(true), $files->count());
        $state = WorkerProcessState::create(null, 0);

        return WorkerProcess::create(
            processHandle: $processHandle,
            paths: $paths,
            metrics: $metrics,
            state: $state
        );
    }

    /**
     * @return iterable<int, string>
     */
    private function buildCommand(
        WorkerProcessPathSet $paths,
        WorkerCommandSecurity $security,
        string $memoryPerWorker
    ): iterable {
        return [
            PHP_BINARY,
            $this->readalizerBin,
            '--_worker',
            '--worker-files=' . $paths->getFilesPath(),
            '--worker-output=' . $paths->getOutputPath(),
            '--worker-progress=' . $paths->getProgressPath(),
            '--worker-token=' . $security->getToken(),
            '--worker-token-file=' . $security->getTokenFile(),
            '--config=' . $this->configPath,
            '--memory-limit=' . $memoryPerWorker,
        ];
    }

    private function writeFileList(PathCollection $files): string
    {
        $path = $this->createTempPath(self::FILE_PREFIX);
        $lines = [];
        foreach ($files as $file) {
            $lines[] = $file;
        }

        file_put_contents($path, implode(self::NEWLINE, $lines) . self::NEWLINE);

        return $path;
    }

    private function createTempPath(string $prefix): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix);
        if ($path === false) {
            $path = sys_get_temp_dir() . '/' . $prefix . '-' . bin2hex(random_bytes(8));
        }

        return $path;
    }

    private function buildMemoryLimitPerWorker(int $jobs): string
    {
        $bytes = $this->memoryLimitConverter->getBytesFromLimit($this->memoryLimit);
        if ($bytes <= 0) {
            return $this->memoryLimit;
        }

        $perWorker = intdiv($bytes, max(1, $jobs));
        return $this->memoryLimitConverter->buildLimitFromBytes($perWorker);
    }

    /**
     * @param iterable<int, string> $command
     * @return \stdClass
     */
    private function createProcessHandle(iterable $command): \stdClass
    {
        $commandList = is_array($command) ? array_values($command) : iterator_to_array($command, false);
        $descriptor = [
            0 => ['file', self::DEV_NULL, 'r'],
            1 => ['file', self::DEV_NULL, 'w'],
            2 => ['file', self::DEV_NULL, 'w'],
        ];
        $env = array_replace($_ENV, [self::INTERNAL_ENV => self::INTERNAL_ENV_VALUE]);
        /** @var array<string, mixed> $env */
        $pipes = [];
        /** @var resource|false $handle */
        $handle = proc_open($commandList, $descriptor, $pipes, null, $env);
        if (!is_resource($handle)) {
            throw new \RuntimeException('Failed to spawn worker process.');
        }

        $container = new \stdClass();
        $container->resource = $handle;
        return $container;
    }
}
