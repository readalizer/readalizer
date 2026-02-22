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
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Exception classes should end with the Exception suffix.
 */
final class ExceptionSuffixRule implements RuleContract
{
    private const EXCEPTION_CLASS = 'Exception';
    private const THROWABLE_CLASS = 'Throwable';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        if ($node->name === null || $node->extends === null) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->isThrowableBase($node->extends)) {
            return RuleViolationCollection::create([]);
        }

        $name = $node->name->toString();

        if (str_ends_with($name, 'Exception')) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf('Exception class "%s" should end with "Exception".', $name),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function isThrowableBase(Name $name): bool
    {
        $base = $name->getLast();

        return $base === self::EXCEPTION_CLASS
          || $base === self::THROWABLE_CLASS
          || str_ends_with($base, self::EXCEPTION_CLASS);
    }
}
