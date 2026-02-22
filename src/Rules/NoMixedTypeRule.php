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
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Avoid the mixed type for parameters, returns, and properties.
 *
 */
final class NoMixedTypeRule implements RuleContract
{
    private const TYPE_MIXED = 'mixed';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class, Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $violations = [];

        foreach ($this->collectTypeNodes($node) as [$type, $line]) {
            if ($type instanceof Identifier && strtolower($type->toString()) === self::TYPE_MIXED) {
                $violations[] = RuleViolation::createFromDetails(
                    message:   'Avoid mixed. Use a specific type.',
                    filePath:  $filePath,
                    line:      $line,
                    ruleClass: self::class,
                );
            }
        }

        return RuleViolationCollection::create($violations);
    }

    /** @return array<array{0: Node, 1: int}> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectTypeNodes(Node $node): array
    {
        $types = [];

        if ($node instanceof Property && $node->type !== null) {
            $types[] = [$node->type, $node->getStartLine()];
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            if ($node->returnType !== null) {
                $types[] = [$node->returnType, $node->getStartLine()];
            }
            foreach ($node->params as $param) {
                if ($param->type !== null) {
                    $types[] = [$param->type, $param->getStartLine()];
                }
            }
        }

        return $types;
    }
}
