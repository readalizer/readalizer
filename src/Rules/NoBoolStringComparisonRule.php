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
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\DNumber;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoBoolStringComparisonRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([
            BinaryOp\Equal::class,
            BinaryOp\NotEqual::class,
            BinaryOp\Identical::class,
            BinaryOp\NotIdentical::class,
        ]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var BinaryOp $node */
        if (!$this->isBoolStringComparison($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Avoid comparing booleans with strings or numbers.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function isBoolStringComparison(BinaryOp $node): bool
    {
        $leftIsBool = $this->isBool($node->left);
        $rightIsBool = $this->isBool($node->right);
        $leftIsStringOrNumber = $this->isStringOrNumber($node->left);
        $rightIsStringOrNumber = $this->isStringOrNumber($node->right);

        if ($leftIsBool && $rightIsStringOrNumber) {
            return true;
        }

        return $rightIsBool && $leftIsStringOrNumber;
    }

    private function isBool(Node $node): bool
    {
        return $node instanceof ConstFetch
            && in_array(strtolower($node->name->toString()), ['true', 'false'], true);
    }

    private function isStringOrNumber(Node $node): bool
    {
        return $node instanceof String_ || $node instanceof LNumber || $node instanceof DNumber;
    }
}
