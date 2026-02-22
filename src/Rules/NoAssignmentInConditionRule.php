<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PhpParser\Node\Stmt\For_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoAssignmentInConditionRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([If_::class, While_::class, For_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $cond = $this->getCondition($node);

        if ($cond === null) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->hasAssignment($cond)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Assignment in condition detected. Split into separate statements.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function getCondition(Node $node): ?Node
    {
        if ($node instanceof If_ || $node instanceof While_) {
            return $node->cond;
        }
        if ($node instanceof For_) {
            return $node->cond[0] ?? null;
        }
        return null;
    }

    private function hasAssignment(Node $node): bool
    {
        $stack = [$node];

        while ($stack) {
            $current = array_pop($stack);
            if ($current instanceof Assign || $current instanceof AssignOp) {
                return true;
            }

            $stack = $this->pushChildNodes($current, $stack);
        }

        return false;
    }

    /**
     * @param array<int, Node> $stack
     * @return array<int, Node>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function pushChildNodes(Node $node, array $stack): array
    {
        foreach ($node->getSubNodeNames() as $name) {
            $child = $node->$name;
            if ($child instanceof Node) {
                $stack[] = $child;
                continue;
            }
            if (is_array($child)) {
                $stack = $this->pushArrayNodes($child, $stack);
            }
        }

        return $stack;
    }

    /**
     * @param array<mixed, mixed> $nodes
     * @param array<int, Node> $stack
     * @return array<int, Node>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function pushArrayNodes(array $nodes, array $stack): array
    {
        foreach ($nodes as $sub) {
            if ($sub instanceof Node) {
                $stack[] = $sub;
            }
        }

        return $stack;
    }
}
