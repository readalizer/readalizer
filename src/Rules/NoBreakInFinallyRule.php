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
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Control flow exits in finally blocks are confusing.
 *
 */
final class NoBreakInFinallyRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([TryCatch::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var TryCatch $node */
        if ($node->finally === null) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->hasBreakOrContinue($node->finally->stmts)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Avoid break/continue inside finally blocks.',
            filePath:  $filePath,
            line:      $node->finally->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    /** @param array<mixed, mixed> $stmts */
    private function hasBreakOrContinue(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Node) {
                continue;
            }
            if ($stmt instanceof Break_ || $stmt instanceof Continue_) {
                return true;
            }

            foreach ($stmt->getSubNodeNames() as $name) {
                $child = $stmt->$name;
                if (is_array($child) && $this->hasBreakOrContinue($child)) {
                    return true;
                }
                if ($child instanceof Node && $this->hasBreakOrContinue([$child])) {
                    return true;
                }
            }
        }

        return false;
    }
}
