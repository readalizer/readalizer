<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Analysis\NodeTypeCollection;
use Millerphp\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt\TryCatch;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoNestedTryRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([TryCatch::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var TryCatch $node */
        if (!$this->hasNestedTry($node->stmts)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Nested try/catch detected. Extract inner logic.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    /** @param array<mixed, mixed> $stmts */
    private function hasNestedTry(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Node) {
                continue;
            }
            if ($stmt instanceof TryCatch) {
                return true;
            }
            foreach ($stmt->getSubNodeNames() as $name) {
                $child = $stmt->$name;
                if (is_array($child) && $this->hasNestedTry($child)) {
                    return true;
                }
                if ($child instanceof Node && $this->hasNestedTry([$child])) {
                    return true;
                }
            }
        }

        return false;
    }
}
