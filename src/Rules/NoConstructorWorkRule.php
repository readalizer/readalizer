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

/**
 * Constructors should be simple assignment and validation only.
 */
final class NoConstructorWorkRule implements RuleContract
{
    private const CONSTRUCTOR_NAME = '__construct';

    public function __construct(private readonly int $maxLines = 10) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($node->name->toString() != self::CONSTRUCTOR_NAME || $node->stmts === null) {
            return RuleViolationCollection::create([]);
        }

        $lines = $node->getEndLine() - $node->getStartLine() + 1;

        if ($lines <= $this->maxLines) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf(
                'Constructor is %d lines long (max %d). Move work into factories or helpers.',
                $lines,
                $this->maxLines
            ),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
