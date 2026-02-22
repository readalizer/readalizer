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

final class NoExecutableCodeInFilesRule implements FileRuleContract
{
    private const STRICT_TYPES = 'strict_types';

    /** @param Stmt[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $line = $this->findExecutableLine($ast);

        if ($line === null) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Executable code detected at top level. Keep files declarative only.',
            filePath:  $filePath,
            line:      $line,
            ruleClass: self::class,
        )]);
    }

    /** @param Stmt[] $ast */
    private function findExecutableLine(array $ast): ?int
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Namespace_) {
                $line = $this->findExecutableLine($stmt->stmts);
                if ($line !== null) {
                    return $line;
                }
                continue;
            }

            if ($this->isAllowedTopLevel($stmt)) {
                continue;
            }

            return $stmt->getStartLine();
        }

        return null;
    }

    private function isAllowedTopLevel(Stmt $stmt): bool
    {
        // Allow declare(strict_types=1); at the top level
        if ($stmt instanceof Stmt\Declare_) {
            foreach ($stmt->declares as $declare) {
                if ($declare->key->name === self::STRICT_TYPES && $declare->value->value === 1) {
                    return true;
                }
            }
        }

        return $stmt instanceof Stmt\Namespace_
            || $stmt instanceof Stmt\Class_
            || $stmt instanceof Stmt\Interface_
            || $stmt instanceof Stmt\Trait_
            || $stmt instanceof Stmt\Function_
            || $stmt instanceof Stmt\Const_
            || $stmt instanceof Stmt\Use_
            || $stmt instanceof Stmt\GroupUse
            || $stmt instanceof Stmt\Nop
            || $stmt instanceof Stmt\InlineHTML;
    }
}
