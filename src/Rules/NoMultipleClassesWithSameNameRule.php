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

final class NoMultipleClassesWithSameNameRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $names = $this->collectTypeNames($ast);

        foreach ($names as $name => $count) {
            if ($count <= 1) {
                continue;
            }

            return RuleViolationCollection::create([RuleViolation::createFromDetails(
                message:   sprintf('Duplicate type name "%s" detected in file.', $name),
                filePath:  $filePath,
                line:      1,
                ruleClass: self::class,
            )]);
        }

        return RuleViolationCollection::create([]);
    }

    /**
     * @param Node[] $ast
     * @return array<string, int>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function collectTypeNames(array $ast): array
    {
        $names = [];

        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Namespace_) {
                $names = $this->mergeCounts($names, $this->collectTypeNames($stmt->stmts));
                continue;
            }

            if ($stmt instanceof Stmt\Class_ || $stmt instanceof Stmt\Interface_ || $stmt instanceof Stmt\Trait_) {
                if ($stmt->name === null) {
                    continue;
                }
                $name = $stmt->name->toString();
                $names[$name] = ($names[$name] ?? 0) + 1;
            }
        }

        return $names;
    }

    /**
     * @param array<string, int> $base
     * @param array<string, int> $extra
     * @return array<string, int>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function mergeCounts(array $base, array $extra): array
    {
        foreach ($extra as $name => $count) {
            $base[$name] = ($base[$name] ?? 0) + $count;
        }

        return $base;
    }
}
