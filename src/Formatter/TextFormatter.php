<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Formatter;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;
use Millerphp\Readalizer\Console\Output;
use Millerphp\Readalizer\Contracts\FormatterContract;

final class TextFormatter implements FormatterContract
{
    private function __construct(private readonly TextStyler $styler)
    {
    }

    public static function create(?bool $useAnsi = null): self
    {
        if ($useAnsi === null) {
            return new self(TextStyler::createAuto());
        }

        return new self(
            $useAnsi ? TextStyler::createWithAnsi() : TextStyler::createWithoutAnsi()
        );
    }

    public function format(RuleViolationCollection $violations): string
    {
        if ($violations->count() === 0) {
            return $this->styler->style('[OK]', 'green') . " No readability violations found.\n";
        }

        $count     = $violations->count();
        $grouped   = $this->groupByFile($violations);
        $lines     = $this->buildFileLines($grouped);
        $lines[]   = sprintf(
            '%s Found %d %s.',
            $this->styler->style('[FAIL]', 'red'),
            $count,
            $count === 1 ? 'violation' : 'violations',
        );

        return implode("\n", $lines) . "\n";
    }

    public function write(RuleViolationCollection $violations, Output $output): void
    {
        if ($violations->count() === 0) {
            $output->writeln($this->styler->style('[OK]', 'green') . ' No readability violations found.');
            return;
        }

        $count   = $violations->count();
        $grouped = $this->groupByFile($violations);

        foreach ($grouped as $file => $fileViolations) {
            $output->writeln($this->styler->style($file, 'bold'));
            $maxLine = $this->maxLineWidth($fileViolations);

            foreach ($fileViolations as $violation) {
                $output->writeln($this->formatViolationLine($violation, $maxLine));
            }

            $output->writeln('');
        }

        $output->writeln(sprintf(
            '%s Found %d %s.',
            $this->styler->style('[FAIL]', 'red'),
            $count,
            $count === 1 ? 'violation' : 'violations',
        ));
    }

    // @readalizer-suppress NoArrayReturnRule
    /** @return array<string, array<int, RuleViolation>> */
    private function groupByFile(RuleViolationCollection $violations): array
    {
        $grouped = [];

        foreach ($violations as $violation) {
            $grouped[$violation->getFilePath()][] = $violation;
        }

        ksort($grouped);

        foreach ($grouped as $file => $fileViolations) {
            usort($fileViolations, fn(RuleViolation $a, RuleViolation $b) => $a->getLine() <=> $b->getLine());
            $grouped[$file] = $fileViolations;
        }

        return $grouped;
    }

    // @readalizer-suppress NoArrayReturnRule
    /**
     * @param array<string, array<int, RuleViolation>> $grouped
     * @return array<int, string>
     */
    private function buildFileLines(array $grouped): array
    {
        $lines = [];

        foreach ($grouped as $file => $fileViolations) {
            $lines[] = $this->styler->style($file, 'bold');
            $maxLine = $this->maxLineWidth($fileViolations);

            foreach ($fileViolations as $violation) {
                $lines[] = $this->formatViolationLine($violation, $maxLine);
            }

            $lines[] = '';
        }

        return $lines;
    }

    /** @param RuleViolation[] $violations */
    private function maxLineWidth(array $violations): int
    {
        $maxLine = 0;

        foreach ($violations as $v) {
            $lineLabel = sprintf('%d', $v->getLine());
            $maxLine = max($maxLine, strlen($lineLabel));
        }

        return $maxLine;
    }

    private function formatViolationLine(RuleViolation $violation, int $maxLine): string
    {
        $lineLabel = $this->styler->style('line', 'dim');
        $lineText  = sprintf('%d', $violation->getLine());
        $lineNum   = $this->styler->style(str_pad($lineText, $maxLine, ' ', STR_PAD_LEFT), 'dim');
        $rule      = $this->styler->style('[' . $this->getShortRuleName($violation->getRuleClass()) . ']', 'cyan');

        return sprintf('  %s %s  %s  %s', $lineLabel, $lineNum, $rule, $violation->getMessage());
    }

    private function getShortRuleName(string $ruleClass): string
    {
        $parts = explode('\\', $ruleClass);
        return end($parts);
    }
}
