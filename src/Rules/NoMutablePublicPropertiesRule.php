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
use PhpParser\Node\Stmt\Property;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoMutablePublicPropertiesRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Property $node */
        if (!($node->flags & Class_::MODIFIER_PUBLIC)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Public properties are not allowed in strict mode.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
