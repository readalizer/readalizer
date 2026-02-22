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
use PhpParser\Node\Stmt\ClassMethod;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Empty methods hide missing behavior.
 */
final class NoEmptyMethodRule implements RuleContract
{
    private const CONSTRUCTOR_NAME = '__construct';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($node->name->toString() === self::CONSTRUCTOR_NAME) {
            return RuleViolationCollection::create([]);
        }

        if ($node->isAbstract() || $node->stmts === null || count($node->stmts) > 0) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Empty method body detected. Implement behavior or remove it.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
