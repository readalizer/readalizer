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

/**
 * Files declaring symbols should live in a namespace.
 */
final class RequireNamespaceRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        if (!$this->hasDeclaredSymbols($ast)) {
            return RuleViolationCollection::create([]);
        }

        if ($this->hasNamespace($ast)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Missing namespace declaration. All symbols should be namespaced.',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    /** @param Node[] $ast */
    private function hasNamespace(array $ast): bool
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Namespace_) {
                return true;
            }
        }

        return false;
    }

    /** @param Node[] $ast */
    private function hasDeclaredSymbols(array $ast): bool
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Class_ || $stmt instanceof Stmt\Interface_ || $stmt instanceof Stmt\Trait_) {
                return true;
            }
            if ($stmt instanceof Stmt\Function_ || $stmt instanceof Stmt\Const_) {
                return true;
            }
            if ($stmt instanceof Stmt\Namespace_ && $this->hasDeclaredSymbols($stmt->stmts)) {
                return true;
            }
        }

        return false;
    }
}
