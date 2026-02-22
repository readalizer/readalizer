<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class InlineSuppressionMap
{
    private const EMPTY_INLINE_RULES = '';

    /**
     * @param array<int, string[]> $map 1-indexed line => suppressed rule names (empty = suppress all)
     */
    private function __construct(private readonly array $map)
    {
    }

    public static function createFromSource(string $code): self
    {
        $builder = new self([]);

        return new self($builder->buildMap($code));
    }

    public function isLineSuppressed(int $line, string $ruleClass): bool
    {
        for ($checkLine = $line; $checkLine >= 1; $checkLine--) {
            if (!array_key_exists($checkLine, $this->map)) {
                continue;
            }

            if ($this->isSuppressionListCoveringRule($this->map[$checkLine], $ruleClass)) {
                return true;
            }
        }

        return false;
    }

    /** @param string[] $suppressed */
    private function isSuppressionListCoveringRule(array $suppressed, string $ruleClass): bool
    {
        if (empty($suppressed)) {
            return true;
        }

        $short = $this->shortName($ruleClass);

        foreach ($suppressed as $s) {
            if (ltrim($s, '\\') === ltrim($ruleClass, '\\') || $s === $short) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string[]>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function buildMap(string $code): array
    {
        $map = [];

        foreach (explode("\n", $code) as $i => $line) {
            if (!preg_match('/@readalizer-suppress(?:\s+(.+))?$/i', $line, $matches)) {
                continue;
            }

            $rawRules = isset($matches[1]) ? trim($matches[1]) : '';
            $map[$i + 1] = $rawRules === self::EMPTY_INLINE_RULES
                ? []
                : array_map('trim', explode(',', $rawRules));
        }

        return $map;
    }

    private function shortName(string $fqcn): string
    {
        $parts = explode('\\', ltrim($fqcn, '\\'));
        return end($parts);
    }
}
