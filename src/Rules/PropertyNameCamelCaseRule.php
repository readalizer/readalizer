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
use PhpParser\Node\Stmt\Property;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Property names should be camelCase.
 */
final class PropertyNameCamelCaseRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Property $node */
        $violations = [];

        foreach ($node->props as $prop) {
            $name = $prop->name->toString();

            if (preg_match('/^[a-z][A-Za-z0-9]*$/', $name) === 1) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   sprintf('Property "%s" should be camelCase.', $name),
                filePath:  $filePath,
                line:      $prop->getStartLine(),
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }
}
