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
use PhpParser\Node\UnionType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Avoid using mixed in union types.
 *
 */
final class NoUnionWithMixedRule implements RuleContract
{
    private const TYPE_MIXED = 'mixed';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class, Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $types = $this->collectTypes($node);

        if (!$this->hasMixedInUnion($types)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Union types should not include mixed. Be explicit about allowable types.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    /** @return array<UnionType> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectTypes(Node $node): array
    {
        $types = [];

        if ($node instanceof Property && $node->type instanceof UnionType) {
            $types[] = $node->type;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            if ($node->returnType instanceof UnionType) {
                $types[] = $node->returnType;
            }
            foreach ($node->params as $param) {
                if ($param->type instanceof UnionType) {
                    $types[] = $param->type;
                }
            }
        }

        return $types;
    }

    /** @param array<UnionType> $types */
    private function hasMixedInUnion(array $types): bool
    {
        foreach ($types as $type) {
            foreach ($type->types as $part) {
                if ($part instanceof Identifier && strtolower($part->toString()) === self::TYPE_MIXED) {
                    return true;
                }
            }
        }

        return false;
    }
}
