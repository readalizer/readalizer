<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node\Stmt;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Avoid global functions; keep code namespaced.
 */
final class NoGlobalFunctionsRule implements FileRuleContract
{
    /** @param array<int, Stmt> $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $functions = $this->collectGlobalFunctions($ast);

        if (empty($functions)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Global function detected. Place functions inside a namespace or class.',
            filePath:  $filePath,
            line:      $functions[0],
            ruleClass: self::class,
        )]);
    }

    /**
     * @param array<int, Stmt> $ast
     * @return array<int, int>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function collectGlobalFunctions(array $ast): array
    {
        $lines = [];

        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Function_) {
                $lines[] = $stmt->getStartLine();
            }
        }

        return $lines;
    }
}
