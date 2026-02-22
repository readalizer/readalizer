<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Enforce a single class/interface/trait per file.
 */
final class SingleClassPerFileRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $count = $this->countTypes($ast);

        if ($count <= 1) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Only one class, interface, or trait should be declared per file.',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    /** @param Node[] $ast */
    private function countTypes(array $ast): int
    {
        $count = 0;

        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Class_ || $stmt instanceof Stmt\Interface_ || $stmt instanceof Stmt\Trait_) {
                $count++;
            }

            if ($stmt instanceof Stmt\Namespace_) {
                $count += $this->countTypes($stmt->stmts);
            }
        }

        return $count;
    }
}
