<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class SingleNamespacePerFileRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $count = $this->countNamespaces($ast);

        if ($count <= 1) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Multiple namespaces detected in one file. Use a single namespace per file.',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    /** @param Node[] $ast */
    private function countNamespaces(array $ast): int
    {
        $count = 0;

        foreach ($ast as $stmt) {
            if ($stmt instanceof Namespace_) {
                $count++;
            }
        }

        return $count;
    }
}
