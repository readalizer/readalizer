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

/**
 * Avoid global constants; keep code namespaced.
 */
final class NoGlobalConstantsRule implements FileRuleContract
{
    /** @param array<int, Stmt> $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $lines = $this->collectGlobalConstants($ast);

        if (empty($lines)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Global constant detected. Prefer namespaced or class constants.',
            filePath:  $filePath,
            line:      $lines[0],
            ruleClass: self::class,
        )]);
    }

    /**
     * @param array<int, Stmt> $ast
     * @return array<int, int>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function collectGlobalConstants(array $ast): array
    {
        $lines = [];

        foreach ($ast as $stmt) {
            if ($stmt instanceof Stmt\Const_) {
                $lines[] = $stmt->getStartLine();
            }
        }

        return $lines;
    }
}
