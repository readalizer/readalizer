<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use PhpParser\Node;

final class SuppressionChecker
{
    private function __construct(
        private readonly InlineSuppressionMap $inlineMap,
        private readonly AttributeSuppressionChecker $attributeChecker,
    ) {}

    public static function create(string $sourceCode): self
    {
        return new self(
            InlineSuppressionMap::createFromSource($sourceCode),
            new AttributeSuppressionChecker(),
        );
    }

    public function isSuppressed(Node $node, RuleViolation $violation, ?Node $methodScope, ?Node $classScope): bool
    {
        $rule = $violation->getRuleClass();

        return $this->inlineMap->isLineSuppressed($violation->getLine(), $rule)
            || $this->attributeChecker->hasSuppressionAttribute($node, $rule)
            || ($methodScope !== null && $this->attributeChecker->hasSuppressionAttribute($methodScope, $rule))
            || ($classScope !== null && $this->attributeChecker->hasSuppressionAttribute($classScope, $rule));
    }

    public function isRuleViolationSuppressed(RuleViolation $violation): bool
    {
        return $this->inlineMap->isLineSuppressed($violation->getLine(), $violation->getRuleClass());
    }
}
