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
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\MatchArm;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoMagicStringRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([
            Equal::class,
            NotEqual::class,
            Identical::class,
            NotIdentical::class,
            Case_::class,
            MatchArm::class,
        ]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        if (!$this->hasMagicStringLiteral($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: 'String literal detected. Use a named constant.',
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function hasMagicStringLiteral(Node $node): bool
    {
        if (
            $node instanceof Equal
              || $node instanceof NotEqual
              || $node instanceof Identical
              || $node instanceof NotIdentical
        ) {
            return $node->left instanceof String_ || $node->right instanceof String_;
        }

        if ($node instanceof Case_) {
            return $node->cond instanceof String_;
        }

        if (
            !$node instanceof MatchArm
            || $node->conds === null
        ) {
            return false;
        }

        foreach ($node->conds as $cond) {
            if ($cond instanceof String_) {
                return true;
            }
        }

        return false;
    }

}
