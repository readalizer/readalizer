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
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoThrowGenericExceptionRule implements RuleContract
{
    private const EXCEPTION_CLASS = 'Exception';
    private const THROWABLE_CLASS = 'Throwable';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Throw_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Throw_ $node */
        $name = $this->getThrownClassName($node);

        if ($name === null) {
            return RuleViolationCollection::create([]);
        }

        if ($name === self::EXCEPTION_CLASS || $name === self::THROWABLE_CLASS) {
            return RuleViolationCollection::create([RuleViolation::createFromDetails(
                message:   'Throwing generic Exception/Throwable is discouraged.',
                filePath:  $filePath,
                line:      $node->getStartLine(),
                ruleClass: self::class,
            )]);
        }

        return RuleViolationCollection::create([]);
    }

    private function getThrownClassName(Throw_ $node): ?string
    {
        if (!$node->expr instanceof Node\Expr\New_) {
            return null;
        }

        if (!$node->expr->class instanceof Name) {
            return null;
        }

        return $node->expr->class->getLast();
    }
}
