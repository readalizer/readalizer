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
 * Prefer ?Type over Type|null.
 *
 */
final class PreferNullableTypeSyntaxRule implements RuleContract
{
    private const TYPE_NULL = 'null';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class, Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        foreach ($this->collectUnionTypes($node) as $union) {
            if ($this->isNullablePair($union)) {
                return RuleViolationCollection::create([RuleViolation::createFromDetails(
                    message:   'Use nullable syntax (?Type) instead of Type|null.',
                    filePath:  $filePath,
                    line:      $node->getStartLine(),
                    ruleClass: self::class,
                )]);
            }
        }

        return RuleViolationCollection::create([]);
    }

    /** @return array<UnionType> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectUnionTypes(Node $node): array
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

    private function isNullablePair(UnionType $union): bool
    {
        if (count($union->types) != 2) {
            return false;
        }

        $hasNull = false;
        $hasOther = false;

        foreach ($union->types as $type) {
            if ($type instanceof Identifier && strtolower($type->toString()) === self::TYPE_NULL) {
                $hasNull = true;
            } else {
                $hasOther = true;
            }
        }

        return $hasNull && $hasOther;
    }
}
