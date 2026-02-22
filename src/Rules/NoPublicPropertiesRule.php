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

/**
 * Public mutable properties break encapsulation and make a class's contract
 * implicit. Use readonly properties or private/protected with accessors.
 *
 * public readonly properties are allowed — they are immutable and explicit.
 */
final class NoPublicPropertiesRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Property $node */
        if (!($node->flags & Class_::MODIFIER_PUBLIC) || ($node->flags & Class_::MODIFIER_READONLY)) {
            return RuleViolationCollection::create([]);
        }

        $names = implode(', ', array_map(fn($p) => '$' . $p->name, $node->props));

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: "Property {$names} is public and mutable. Prefer readonly or a private/protected and accessors.",
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
