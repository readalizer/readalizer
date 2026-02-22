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
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use PhpParser\Node\Stmt\For_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Deep boolean expressions are hard to parse.
 */
final class NoDeepBooleanExpressionRule implements RuleContract
{
    public function __construct(private readonly int $maxConditions = 3) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([If_::class, While_::class, For_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $expr = $this->extractCondition($node);

        if ($expr === null) {
            return RuleViolationCollection::create([]);
        }

        $count = $this->countBooleanOps($expr);

        if ($count <= $this->maxConditions) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf(
                'Condition has %d boolean operators (max %d). Extract into named variables.',
                $count,
                $this->maxConditions
            ),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function extractCondition(Node $node): ?Node
    {
        if ($node instanceof If_ || $node instanceof While_) {
            return $node->cond;
        }

        if ($node instanceof For_) {
            return $node->cond[0] ?? null;
        }

        return null;
    }

    private function countBooleanOps(Node $node): int
    {
        $count = 0;

        if ($node instanceof BinaryOp\BooleanAnd || $node instanceof BinaryOp\BooleanOr) {
            $count++;
            $count += $this->countBooleanOps($node->left);
            $count += $this->countBooleanOps($node->right);
        }

        return $count;
    }
}
