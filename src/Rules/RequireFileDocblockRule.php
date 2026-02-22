<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class RequireFileDocblockRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        if (!$this->hasDeclaredSymbols($ast)) {
            return RuleViolationCollection::create([]);
        }

        if ($this->hasFileDocblock($ast)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'File-level docblock required for symbol files.',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    /** @param Node[] $ast */
    private function hasDeclaredSymbols(array $ast): bool
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Class_ || $stmt instanceof Stmt\Interface_ || $stmt instanceof Stmt\Trait_) {
                return true;
            }
            if ($stmt instanceof Stmt\Namespace_ && $this->hasDeclaredSymbols($stmt->stmts)) {
                return true;
            }
        }

        return false;
    }

    /** @param Node[] $ast */
    private function hasFileDocblock(array $ast): bool
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Declare_) {
                if ($stmt->getDocComment() !== null) {
                    return true;
                }
                continue;
            }
            if ($stmt instanceof Stmt\Nop) {
                continue;
            }

            return $stmt->getDocComment() !== null;
        }

        return false;
    }
}
