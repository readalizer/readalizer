<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Blanket suppressions hide real problems.
 */
final class NoSuppressAllRule implements FileRuleContract
{
    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $violations = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return RuleViolationCollection::create([]);
        }

        foreach ($lines as $i => $line) {
            if ($this->isSuppressAllComment($line) || $this->isSuppressAllAttribute($line)) {
                $violations[] = RuleViolation::createFromDetails(
                    message:   'Suppress-all detected. Always name specific rules to suppress.',
                    filePath:  $filePath,
                    line:      $i + 1,
                    ruleClass: self::class,
                );
            }
        }

        return RuleViolationCollection::create($violations);
    }

    private function isSuppressAllComment(string $line): bool
    {
        return preg_match('/^\s*(\/\/|#)\s*@readalizer-suppress\s*$/i', $line) === 1;
    }

    private function isSuppressAllAttribute(string $line): bool
    {
        return preg_match('/^\s*#\[\s*Suppress\s*\]\s*$/', $line) === 1;
    }
}
