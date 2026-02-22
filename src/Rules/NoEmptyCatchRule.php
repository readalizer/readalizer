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
use PhpParser\Node\Stmt\Catch_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Empty catch blocks hide errors.
 */
final class NoEmptyCatchRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Catch_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Catch_ $node */
        if (count($node->stmts) > 0) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Empty catch block detected. Handle the exception or rethrow.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
