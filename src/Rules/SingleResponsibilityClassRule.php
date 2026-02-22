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
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class SingleResponsibilityClassRule implements RuleContract
{
    use HasMagicMethods;

    public function __construct(private readonly int $maxPublicMethods = 8) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        if ($node->name === null) {
            return RuleViolationCollection::create([]);
        }

        $count = $this->countPublicMethods($node);

        if ($count <= $this->maxPublicMethods) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf(
                'Class "%s" has %d public methods (max %d).',
                $node->name,
                $count,
                $this->maxPublicMethods
            ),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function countPublicMethods(Class_ $node): int
    {
        $count = 0;

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod && $stmt->isPublic()) {
                if ($this->isMagicMethod($stmt)) {
                    continue;
                }
                $count++;
            }
        }

        return $count;
    }
}
