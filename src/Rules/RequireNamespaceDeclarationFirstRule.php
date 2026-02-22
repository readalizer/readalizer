<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node\Stmt;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class RequireNamespaceDeclarationFirstRule implements FileRuleContract
{
    /** @param Stmt[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        if (!$this->hasNamespace($ast)) {
            return RuleViolationCollection::create([]);
        }

        $first = $this->getfirstMeaningfulStatement($ast);

        if ($first instanceof Stmt\Namespace_) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: 'Namespace declaration should be the first statement after declare.',
            filePath: $filePath,
            line: $first?->getStartLine() ?? 1,
            ruleClass: self::class,
        )]);
    }

    /** @param Stmt[] $ast */
    private function hasNamespace(array $ast): bool
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Namespace_) {
                return true;
            }
        }

        return false;
    }

    /** @param Stmt[] $ast */
    private function getfirstMeaningfulStatement(array $ast): ?Stmt
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Declare_ || $stmt instanceof Stmt\Nop) {
                continue;
            }
            return $stmt;
        }

        return null;
    }
}
