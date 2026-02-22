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
use PhpParser\Node\Stmt\Class_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Concrete classes should be declared final to prevent unintended inheritance.
 *
 * Favour composition over inheritance. Abstract classes are exempt by
 * definition. Anonymous classes are also exempt.
 */
final class FinalClassRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        if ($node->name === null || $node->isFinal() || $node->isAbstract()) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([
            RuleViolation::createFromDetails(
                message: sprintf(
                    'Class "%s" should be declared final. Favour composition over inheritance.',
                    $node->name
                ),
                filePath: $filePath,
                line: $node->getStartLine(),
                ruleClass: self::class,
            ),
        ]);
    }
}
