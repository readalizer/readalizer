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
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoNullableMixedRule implements RuleContract
{
    private const TYPE_MIXED = 'mixed';
    private const TYPE_NULL = 'null';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class, Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        if (!$this->hasNullableMixed($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Nullable mixed detected. Avoid mixed and use explicit types.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function hasNullableMixed(Node $node): bool
    {
        $types = $this->collectTypes($node);

        foreach ($types as $type) {
            if ($type instanceof NullableType && $type->type instanceof Identifier
                && strtolower($type->type->toString()) === self::TYPE_MIXED) {
                return true;
            }
            if ($type instanceof UnionType && $this->hasUnionMixedAndNull($type)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<Node> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectTypes(Node $node): array
    {
        $types = [];

        if ($node instanceof Property && $node->type !== null) {
            $types[] = $node->type;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            if ($node->returnType !== null) {
                $types[] = $node->returnType;
            }
            foreach ($node->params as $param) {
                if ($param->type !== null) {
                    $types[] = $param->type;
                }
            }
        }

        return $types;
    }

    private function hasUnionMixedAndNull(UnionType $type): bool
    {
        $hasMixed = false;
        $hasNull = false;

        foreach ($type->types as $part) {
            if ($part instanceof Identifier) {
                $name = strtolower($part->toString());
                if ($name === self::TYPE_MIXED) {
                    $hasMixed = true;
                }
                if ($name === self::TYPE_NULL) {
                    $hasNull = true;
                }
            }
        }

        return $hasMixed && $hasNull;
    }
}
