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
use PhpParser\Node\Expr\Exit_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Exiting early bypasses calling code and tests.
 */
final class NoExitRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Exit_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Avoid exit/die. Throw exceptions or return error values instead.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
