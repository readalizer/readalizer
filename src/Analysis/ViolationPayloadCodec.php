<?php

/**
 * Encodes and decodes rule violations for worker communication.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class ViolationPayloadCodec
{
    private const PAYLOAD_KEY = 'violations';
    private const EMPTY_STRING = '';

    public function formatPayload(RuleViolationCollection $violations): string
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

        $payload = json_encode([self::PAYLOAD_KEY => $items], JSON_UNESCAPED_SLASHES);
        return is_string($payload) ? $payload : self::EMPTY_STRING;
    }

    public function parsePayload(string $payload): RuleViolationCollection
    {
        $items = $this->buildItems($payload);
        if ($items === null) {
            return RuleViolationCollection::create([]);
        }

        return $this->buildViolations($items);
    }

    /**
     * @param array{file: string, line: int, message: string, rule: string} $item
     */
    private function createViolation(array $item): RuleViolation
    {
        return RuleViolation::createFromDetails(
            message: $item['message'],
            filePath: $item['file'],
            line: $item['line'],
            ruleClass: $item['rule'],
        );
    }

    /**
     * @param array<string, mixed> $item
     */
    private function isPayloadItem(array $item): bool
    {
        if (!isset($item['message'], $item['file'], $item['line'], $item['rule'])) {
            return false;
        }

        return is_string($item['message'])
            && is_string($item['file'])
            && is_int($item['line'])
            && is_string($item['rule']);
    }

    /**
     * @return PayloadItemCollection|null
     */
    private function buildItems(string $payload): ?PayloadItemCollection
    {
        if ($payload === self::EMPTY_STRING) {
            return null;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($payload, true);
        if (!is_array($data) || !isset($data[self::PAYLOAD_KEY]) || !is_array($data[self::PAYLOAD_KEY])) {
            return null;
        }

        $items = $this->normalizeItems(array_values($data[self::PAYLOAD_KEY]));

        return PayloadItemCollection::create($items);
    }

    private function buildViolations(PayloadItemCollection $items): RuleViolationCollection
    {
        $violations = [];
        foreach ($items as $item) {
            if (!$this->isPayloadItem($item)) {
                continue;
            }

            /** @var array{file: string, line: int, message: string, rule: string} $item */
            $violations[] = $this->createViolation($item);
        }

        return RuleViolationCollection::create($violations);
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, array<string, mixed>>
     */
    // @readalizer-suppress NoArrayReturnRule
    // @readalizer-suppress FunctionVerbNameRule
    private function normalizeItems(array $items): array
    {
        $normalizedItems = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = $this->normalizeItem($item);
            if ($normalized === null) {
                continue;
            }

            $normalizedItems[] = $normalized;
        }

        return $normalizedItems;
    }

    /**
     * @param array<mixed, mixed> $item
     * @return array<string, mixed>|null
     */
    // @readalizer-suppress NoArrayReturnRule
    // @readalizer-suppress FunctionVerbNameRule
    private function normalizeItem(array $item): ?array
    {
        $normalized = [];
        foreach ($item as $key => $value) {
            if (!is_string($key)) {
                return null;
            }
            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
