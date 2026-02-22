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
use PhpParser\Node\Expr\Ternary;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoImplicitTernaryRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Ternary::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Ternary $node */
        if ($node->if !== null) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Implicit ternary detected (?:). Use explicit condition.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
