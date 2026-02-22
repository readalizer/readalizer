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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoYodaConditionsRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([
            BinaryOp\Equal::class,
            BinaryOp\NotEqual::class,
            BinaryOp\Identical::class,
            BinaryOp\NotIdentical::class,
            BinaryOp\Smaller::class,
            BinaryOp\SmallerOrEqual::class,
            BinaryOp\Greater::class,
            BinaryOp\GreaterOrEqual::class,
        ]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var BinaryOp $node */
        if (!$this->isYoda($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Yoda condition detected. Put variables on the left.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function isYoda(BinaryOp $node): bool
    {
        return $this->isLiteral($node->left) && $this->isVariableLike($node->right);
    }

    private function isLiteral(Node $node): bool
    {
        return $node instanceof Scalar;
    }

    private function isVariableLike(Node $node): bool
    {
        return $node instanceof Variable || $node instanceof PropertyFetch || $node instanceof MethodCall;
    }
}
