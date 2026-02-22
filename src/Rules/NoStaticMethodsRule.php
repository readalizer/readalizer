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
use PhpParser\Node\Stmt\ClassMethod;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoStaticMethodsRule implements RuleContract
{
    /** @var string[] */
    private const ALLOWED_PREFIXES = ['from', 'create', 'make'];

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if (!$node->isStatic()) {
            return RuleViolationCollection::create([]);
        }

        if ($this->isAllowedFactory($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Static methods are discouraged. Prefer instance methods and dependency injection.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function isAllowedFactory(ClassMethod $node): bool
    {
        if (!$node->isPublic()) {
            return false;
        }

        $name = strtolower($node->name->toString());

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
