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
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class RequireNamedConstructorRule implements RuleContract
{
    private const CONSTRUCTOR_NAME = '__construct';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        if ($this->isInternalRuleFile($filePath)) {
            return RuleViolationCollection::create([]);
        }

        if ($node->name === null) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->hasPublicConstructor($node)) {
            return RuleViolationCollection::create([]);
        }

        if ($this->hasNamedConstructor($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf('Class "%s" should provide a named constructor.', $node->name),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function isInternalRuleFile(string $filePath): bool
    {
        return str_contains($filePath, DIRECTORY_SEPARATOR . 'Rules' . DIRECTORY_SEPARATOR)
            || str_contains($filePath, DIRECTORY_SEPARATOR . 'Rulesets' . DIRECTORY_SEPARATOR);
    }

    private function hasPublicConstructor(Class_ $node): bool
    {
        foreach ($node->stmts as $stmt) {
            if (
                $stmt instanceof ClassMethod
                  && $stmt->name->toString() == self::CONSTRUCTOR_NAME
                  && $stmt->isPublic()
            ) {
                return true;
            }
        }

        return false;
    }

    private function hasNamedConstructor(Class_ $node): bool
    {
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof ClassMethod || !$stmt->isStatic() || !$stmt->isPublic()) {
                continue;
            }
            $name = strtolower($stmt->name->toString());
            if (str_starts_with($name, 'from') || str_starts_with($name, 'create') || str_starts_with($name, 'make')) {
                return true;
            }
        }

        return false;
    }
}
