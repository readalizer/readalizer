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
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class RequireImmutableValueObjectRule implements RuleContract
{
    private const CONSTRUCTOR_NAME = '__construct';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        if ($node->name === null || $node->isReadonly()) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->looksLikeValueObject($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('Value object "%s" should be readonly.', $node->name),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function looksLikeValueObject(Class_ $node): bool
    {
        $methods = 0;
        $properties = 0;

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Property) {
                $properties++;
            } elseif ($stmt instanceof ClassMethod) {
                if ($stmt->name->toString() === self::CONSTRUCTOR_NAME) {
                    continue;
                }
                $methods++;
            }
        }

        return $properties > 0 && $methods === 0;
    }
}
