<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoTrailingBlankLinesRule implements FileRuleContract
{
    private const BLANK_LINE = '';

    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false || empty($lines)) {
            return RuleViolationCollection::create([]);
        }

        $lastIndex = count($lines) - 1;
        $lastNonBlank = $this->findLastNonBlank($lines);

        if ($lastNonBlank === $lastIndex) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Trailing blank lines detected at end of file.',
            filePath:  $filePath,
            line:      $lastIndex + 1,
            ruleClass: self::class,
        )]);
    }

    /** @param string[] $lines */
    private function findLastNonBlank(array $lines): int
    {
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (trim($lines[$i]) !== self::BLANK_LINE) {
                return $i;
            }
        }

        return 0;
    }
}
