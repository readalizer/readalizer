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
use PhpParser\Node\Stmt\Echo_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Echo in libraries makes output harder to control.
 */
final class NoEchoRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Echo_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Avoid echo. Return values and let callers decide how to output.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
