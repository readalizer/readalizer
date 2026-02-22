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
use PhpParser\Node\Expr\Exit_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

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
