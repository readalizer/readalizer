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
use PhpParser\Node\Expr\Ternary;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Nested ternary expressions are difficult to parse visually and are a common
 * source of bugs (PHP 8 made nested ternaries without explicit parentheses a
 * fatal error for good reason).
 *
 * Extract the inner expression into a named variable, or use if/else.
 */
final class NoNestedTernaryRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Ternary::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Ternary $node */
        $hasNestedTernary = $node->cond instanceof Ternary
            || $node->if instanceof Ternary
            || $node->else instanceof Ternary;

        if (!$hasNestedTernary) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([
            RuleViolation::createFromDetails(
                message:   'Nested ternary expressions are hard to read. Extract into a named variable or use if/else.',
                filePath:  $filePath,
                line:      $node->getStartLine(),
                ruleClass: self::class,
            ),
        ]);
    }
}
