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
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class RequireIterableValueTypeRule implements RuleContract
{
    private const TYPE_ARRAY = 'array';
    private const TYPE_ITERABLE = 'iterable';
    private const TAG_PATTERN = '/@'
        . '(param|return|var|phpstan-param|phpstan-return|phpstan-var|psalm-param|psalm-return|psalm-var)'
        . '\s+([^\s]+)/i';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class, Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        if (!$this->hasIterableType($node)) {
            return RuleViolationCollection::create([]);
        }

        if ($this->hasDocValueType($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Iterable type missing value type annotation (e.g. array<int>).',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function hasIterableType(Node $node): bool
    {
        $types = $this->collectTypes($node);

        foreach ($types as $type) {
            if ($type instanceof Identifier) {
                $name = strtolower($type->toString());
                if ($name === self::TYPE_ARRAY || $name === self::TYPE_ITERABLE) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @return array<Identifier> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectTypes(Node $node): array
    {
        $types = [];

        if ($node instanceof Property && $node->type instanceof Identifier) {
            $types[] = $node->type;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            if ($node->returnType instanceof Identifier) {
                $types[] = $node->returnType;
            }
            foreach ($node->params as $param) {
                if ($param->type instanceof Identifier) {
                    $types[] = $param->type;
                }
            }
        }

        return $types;
    }

    private function hasDocValueType(Node $node): bool
    {
        $doc = $node->getDocComment()?->getText();

        if ($doc === null) {
            return false;
        }

        if ($this->hasTaggedDocValueType($doc)) {
            return true;
        }

        return $this->hasGenericDocValueType($doc);
    }

    private function hasTaggedDocValueType(string $doc): bool
    {
        if (!preg_match_all(self::TAG_PATTERN, $doc, $matches, PREG_SET_ORDER)) {
            return false;
        }

        foreach ($matches as $match) {
            if ($this->hasValueTypeInType($match[2])) {
                return true;
            }
        }

        return false;
    }

    private function hasGenericDocValueType(string $doc): bool
    {
        return str_contains($doc, '[]')
            || preg_match('/\b(array|iterable|list|non-empty-array|non-empty-list)\s*</i', $doc) === 1
            || str_contains($doc, 'array{');
    }

    private function hasValueTypeInType(string $type): bool
    {
        return str_contains($type, '[]')
            || str_contains($type, '<')
            || str_contains($type, '{');
    }
}
