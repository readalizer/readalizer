<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Trailing whitespace adds noise in diffs and reviews.
 */
final class NoTrailingWhitespaceRule implements FileRuleContract
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
            if (preg_match('/\s+$/', $line) !== 1) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   'Trailing whitespace detected. Remove extra spaces at line end.',
                filePath:  $filePath,
                line:      $i + 1,
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }
}
