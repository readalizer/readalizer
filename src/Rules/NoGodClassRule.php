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
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\ClassMethod;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Large classes with many members are hard to maintain.
 */
final class NoGodClassRule implements RuleContract
{
    public function __construct(
        private readonly int $maxMethods = 10,
        private readonly int $maxProperties = 10,
    ) {}

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

        [$methods, $properties] = $this->countMembers($node);

        if ($methods <= $this->maxMethods && $properties <= $this->maxProperties) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([$this->buildViolation($node, $filePath, $methods, $properties)]);
    }

    private function buildViolation(Class_ $node, string $filePath, int $methods, int $properties): RuleViolation
    {
        $message = sprintf(
            'Class "%s" has %d methods and %d properties (max %d/%d). Consider splitting.',
            $node->name,
            $methods,
            $properties,
            $this->maxMethods,
            $this->maxProperties,
        );

        return RuleViolation::createFromDetails(
            message:   $message,
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        );
    }

    /** @return array<int, int> */
    private function countMembers(Class_ $node): array // @readalizer-suppress NoArrayReturnRule
    {
        $methods = 0;
        $properties = 0;

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod) {
                $methods++;
            } elseif ($stmt instanceof Property || $stmt instanceof ClassConst) {
                $properties++;
            }
        }

        return [$methods, $properties];
    }
}
