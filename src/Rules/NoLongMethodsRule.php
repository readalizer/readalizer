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
use PhpParser\Node\Stmt\ClassMethod;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Methods whose body exceeds a configured line limit are doing too much.
 * Extract responsibilities into focused helper methods or separate classes.
 */
final class NoLongMethodsRule implements RuleContract
{
    use HasMagicMethods;

    public function __construct(private readonly int $maxLines = 30) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        if ($node->stmts === null) {
            return RuleViolationCollection::create([]); // abstract method
        }

        $lineCount = $node->getEndLine() - $node->getStartLine() + 1;

        if ($lineCount <= $this->maxLines) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf(
                'Method "%s" is %d lines long (max %d).',
                $node->name->toString(),
                $lineCount,
                $this->maxLines
            ),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
