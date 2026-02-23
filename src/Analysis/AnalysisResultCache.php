<?php

/**
 * Persistent per-file cache for analysis violations.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use Readalizer\Readalizer\Attributes\Suppress;

#[Suppress(
    \Readalizer\Readalizer\Rules\MaxClassLengthRule::class,
    \Readalizer\Readalizer\Rules\NoGodClassRule::class,
    \Readalizer\Readalizer\Rules\NoStaticMethodsRule::class,
)]
final class AnalysisResultCache
{
    private const VERSION = 1;
    private const EMPTY_STRING = '';
    private const CURRENT_DIR = '.';
    private const FILE_OPEN_MODE = 'c+';

    /** @var array<string, array{mtime: int, size: int, violations: array<int, array<string, mixed>>}> */
    private array $entries = [];

    /** @var array<string, true> */
    private array $dirtyKeys = [];

    private bool $loaded = false;

    private function __construct(
        private readonly string $path,
        private readonly string $rulesFingerprint
    ) {
    }

    public static function create(string $path, RuleCollection $rules): self
    {
        return new self($path, self::buildRulesFingerprint($rules));
    }

    public function get(string $filePath): ?RuleViolationCollection
    {
        $this->loadIfNeeded();
        $stat = @stat($filePath);
        if (!is_array($stat)) {
            return null;
        }

        $entry = $this->entries[$filePath] ?? null;
        if (!is_array($entry)) {
            return null;
        }

        $mtime = isset($stat['mtime']) ? (int) $stat['mtime'] : -1;
        $size = isset($stat['size']) ? (int) $stat['size'] : -1;
        if ($entry['mtime'] !== $mtime || $entry['size'] !== $size) {
            return null;
        }

        return $this->buildViolationsFromArray($entry['violations']);
    }

    public function saveFileResult(string $filePath, RuleViolationCollection $violations): void
    {
        $this->loadIfNeeded();
        $stat = @stat($filePath);
        if (!is_array($stat)) {
            return;
        }

        $this->entries[$filePath] = [
            'mtime' => isset($stat['mtime']) ? (int) $stat['mtime'] : -1,
            'size' => isset($stat['size']) ? (int) $stat['size'] : -1,
            'violations' => $this->buildArrayFromViolations($violations),
        ];
        $this->dirtyKeys[$filePath] = true;
    }

    #[Suppress(
        \Readalizer\Readalizer\Rules\NoLongMethodsRule::class,
        \Readalizer\Readalizer\Rules\MaxMethodStatementsRule::class,
    )]
    public function saveChanges(): void
    {
        if ($this->dirtyKeys === []) {
            return;
        }

        $dir = dirname($this->path);
        if ($dir !== self::EMPTY_STRING && $dir !== self::CURRENT_DIR && !is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $handle = @fopen($this->path, self::FILE_OPEN_MODE);
        if (!is_resource($handle)) {
            return;
        }

        if (!@flock($handle, LOCK_EX)) {
            fclose($handle);
            return;
        }

        $existing = stream_get_contents($handle);
        $data = $this->parseCachePayload(is_string($existing) ? $existing : self::EMPTY_STRING);

        if ($data === null || ($data['rules_fingerprint'] ?? null) !== $this->rulesFingerprint) {
            $data = $this->createEmptyData();
        }

        /** @var array<string, mixed> $data */
        $data['version'] = self::VERSION;
        $data['rules_fingerprint'] = $this->rulesFingerprint;
        $entries = isset($data['entries']) && is_array($data['entries']) ? $data['entries'] : [];
        foreach (array_keys($this->dirtyKeys) as $filePath) {
            $entries[$filePath] = $this->entries[$filePath];
        }
        $data['entries'] = $entries;

        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        if (is_string($payload)) {
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $payload);
        }

        fflush($handle);
        @flock($handle, LOCK_UN);
        fclose($handle);

        $this->dirtyKeys = [];
    }

    #[Suppress(\Readalizer\Readalizer\Rules\NoLongMethodsRule::class)]
    private function loadIfNeeded(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;
        $contents = @file_get_contents($this->path);
        if (!is_string($contents)) {
            return;
        }

        $data = $this->parseCachePayload($contents);
        if ($data === null) {
            return;
        }

        if (($data['rules_fingerprint'] ?? null) !== $this->rulesFingerprint) {
            return;
        }

        $entries = $data['entries'] ?? null;
        if (!is_array($entries)) {
            return;
        }

        foreach ($entries as $filePath => $entry) {
            if (!is_string($filePath) || !is_array($entry)) {
                continue;
            }

            if (
                !isset($entry['mtime'], $entry['size'], $entry['violations'])
                || !is_int($entry['mtime'])
                || !is_int($entry['size'])
                || !is_array($entry['violations'])
            ) {
                continue;
            }

            /** @var array<int, array<string, mixed>> $violations */
            $violations = array_values(array_filter($entry['violations'], 'is_array'));

            $this->entries[$filePath] = [
                'mtime' => $entry['mtime'],
                'size' => $entry['size'],
                'violations' => $violations,
            ];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    // @readalizer-suppress NoArrayReturnRule
    private function parseCachePayload(string $contents): ?array
    {
        if ($contents === self::EMPTY_STRING) {
            return null;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($contents, true);
        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * @return array{version: int, rules_fingerprint: string, entries: array<string, mixed>}
     */
    // @readalizer-suppress NoArrayReturnRule
    private function createEmptyData(): array
    {
        return [
            'version' => self::VERSION,
            'rules_fingerprint' => $this->rulesFingerprint,
            'entries' => [],
        ];
    }

    /**
     * @return array<int, array{file: string, line: int, message: string, rule: string}>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function buildArrayFromViolations(RuleViolationCollection $violations): array
    {
        $items = [];
        foreach ($violations as $violation) {
            $items[] = [
                'file' => $violation->getFilePath(),
                'line' => $violation->getLine(),
                'message' => $violation->getMessage(),
                'rule' => $violation->getRuleClass(),
            ];
        }

        return $items;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function buildViolationsFromArray(array $items): RuleViolationCollection
    {
        $violations = [];
        foreach ($items as $item) {
            $normalized = $this->parseCachedViolationItem($item);
            if ($normalized === null) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                $normalized['message'],
                $normalized['file'],
                $normalized['line'],
                $normalized['rule']
            );
        }

        return RuleViolationCollection::create($violations);
    }

    private static function buildRulesFingerprint(RuleCollection $rules): string
    {
        $parts = [];
        foreach ($rules as $rule) {
            $parts[] = self::buildRuleFingerprint($rule);
        }

        return sha1(implode('|', $parts));
    }

    #[Suppress(
        \Readalizer\Readalizer\Rules\NoCatchGenericExceptionRule::class,
        \Readalizer\Readalizer\Rules\NoEmptyCatchRule::class,
    )]
    private static function buildRuleFingerprint(object $rule): string
    {
        try {
            $serialized = @serialize($rule);
            if ($serialized !== self::EMPTY_STRING) {
                return sha1($rule::class . '|' . $serialized);
            }
        } catch (\Throwable) {
        }

        return sha1($rule::class);
    }

    /**
     * @param array<mixed, mixed> $item
     * @return array{file: string, line: int, message: string, rule: string}|null
     */
    // @readalizer-suppress NoArrayReturnRule
    private function parseCachedViolationItem(array $item): ?array
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
