<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt\InlineHTML;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Avoid mixing HTML with PHP in source files.
 */
final class NoMixedPhpHtmlRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $violations = [];
        foreach ($ast as $stmt) {
            if ($stmt instanceof InlineHTML) {
                // Ignore shebang line in executable files
                if ($stmt->getStartLine() === 1 && str_starts_with($stmt->value, '#!')) {
                    continue;
                }
                $violations[] = RuleViolation::createFromDetails(
                    message:   'Inline HTML detected. Keep PHP files focused on PHP only.',
                    filePath:  $filePath,
                    line:      $stmt->getStartLine(),
                    ruleClass: self::class,
                );
            }
            if ($stmt instanceof Node\Stmt\Namespace_ && $this->hasInlineHtml($stmt->stmts)) {
                // Recursive check for inline HTML within namespaces
                // This is a simplified check, ideally would return violations per line
                $violations[] = RuleViolation::createFromDetails(
                    message:   'Inline HTML detected. Keep PHP files focused on PHP only.',
                    filePath:  $filePath,
                    line:      $stmt->getStartLine(),
                    ruleClass: self::class,
                );
            }
        }
        return RuleViolationCollection::create($violations);
    }

    /** @param Node[] $ast */
    private function hasInlineHtml(array $ast): bool
    {
        // This helper method is no longer strictly needed but kept for context.
        // The logic is now inline in processFile.
        foreach ($ast as $stmt) {
            if ($stmt instanceof InlineHTML) {
                // Ignore shebang line
                if ($stmt->getStartLine() === 1 && str_starts_with($stmt->value, '#!')) {
                    continue;
                }
                return true;
            }
            if ($stmt instanceof Node\Stmt\Namespace_ && $this->hasInlineHtml($stmt->stmts)) {
                return true;
            }
        }
        return false;
    }
}
