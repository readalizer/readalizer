<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr;

final class AttributeSuppressionChecker
{
    private const SUPPRESS_SHORT = 'Suppress';
    private const SUPPRESS_FQCN = 'Millerphp\\Readalizer\\Attributes\\Suppress';
    private const CLASS_CONST_NAME = 'class';

    public function hasSuppressionAttribute(Node $node, string $ruleClass): bool
    {
        if (!property_exists($node, 'attrGroups')) {
            return false;
        }

        if (!is_array($node->attrGroups)) {
            return false;
        }

        /** @var Node\AttributeGroup[] $attrGroups */
        $attrGroups = $node->attrGroups;

        foreach ($attrGroups as $attrGroup) {
            if (!is_array($attrGroup->attrs)) {
                continue;
            }
            /** @var Node\Attribute[] $attrs */
            $attrs = $attrGroup->attrs;
            foreach ($attrs as $attr) {
                if ($this->doesAttributeSuppressRule($attr, $ruleClass)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function doesAttributeSuppressRule(Node\Attribute $attr, string $ruleClass): bool
    {
        if (!$this->isSuppressAttrName($attr->name)) {
            return false;
        }

        if (empty($attr->args)) {
            return true;
        }

        foreach ($attr->args as $arg) {
            if ($this->doesArgMatchRule($arg->value, $ruleClass)) {
                return true;
            }
        }

        return false;
    }

    private function isSuppressAttrName(Node\Name $name): bool
    {
        return $name->getLast() === self::SUPPRESS_SHORT
            || ltrim($name->toString(), '\\') === self::SUPPRESS_FQCN;
    }

    private function doesArgMatchRule(Node\Expr $expr, string $ruleClass): bool
    {
        if (!$expr instanceof Expr\ClassConstFetch) {
            return false;
        }

        if (!$expr->name instanceof Identifier || $expr->name->toString() !== self::CLASS_CONST_NAME) {
            return false;
        }

        if (!$expr->class instanceof Node\Name) {
            return false;
        }

        $supplied = ltrim($expr->class->toString(), '\\');
        $target   = ltrim($ruleClass, '\\');

        return $supplied === $target || $this->shortName($supplied) === $this->shortName($target);
    }

    private function shortName(string $fqcn): string
    {
        $parts = explode('\\', ltrim($fqcn, '\\'));
        return end($parts);
    }
}
