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
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoProtectedPropertiesRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Property $node */
        if (!($node->flags & Class_::MODIFIER_PROTECTED)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Protected properties are discouraged. Use private properties and explicit APIs.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
