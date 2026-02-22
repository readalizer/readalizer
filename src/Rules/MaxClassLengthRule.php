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
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * A class that spans too many lines is likely handling too many concerns.
 * Split responsibilities across smaller, focused classes.
 */
final class MaxClassLengthRule implements RuleContract
{
    public function __construct(private readonly int $maxLines = 170) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        $lineCount = $node->getEndLine() - $node->getStartLine() + 1;

        if ($lineCount <= $this->maxLines) {
            return RuleViolationCollection::create([]);
        }

        $name = $node->name?->toString() ?? '(anonymous)';

        $message = sprintf(
            'Class "%s" is %d lines long (max %d). Consider splitting responsibilities.',
            $name,
            $lineCount,
            $this->maxLines,
        );

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   $message,
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
