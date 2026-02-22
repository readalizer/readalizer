<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use Readalizer\Readalizer\Rules\Concerns\HasMagicMethods;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoNegativeBooleanNameRule implements RuleContract
{
    use HasMagicMethods;
    private const TYPE_BOOL = 'bool';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        if ($node instanceof ClassMethod && $this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->isBoolReturn($node)) {
            return RuleViolationCollection::create([]);
        }

        if (!$node->name instanceof Identifier) {
            return RuleViolationCollection::create([]);
        }

        $name = strtolower($node->name->toString());

        if ($this->isNegativeName($name)) {
            return RuleViolationCollection::create([RuleViolation::createFromDetails(
                message:   'Avoid negative boolean names (e.g. isNotX). Use positive naming.',
                filePath:  $filePath,
                line:      $node->getStartLine(),
                ruleClass: self::class,
            )]);
        }

        return RuleViolationCollection::create([]);
    }

    private function isBoolReturn(Node $node): bool
    {
        return $node->returnType instanceof Identifier
            && strtolower($node->returnType->toString()) === self::TYPE_BOOL;
    }

    private function isNegativeName(string $name): bool
    {
        return str_starts_with($name, 'isnot')
            || str_starts_with($name, 'hasno')
            || str_starts_with($name, 'cannot')
            || str_starts_with($name, 'not');
    }
}
