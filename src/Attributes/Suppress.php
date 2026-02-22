<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Attributes;

use Readalizer\Readalizer\Contracts\RuleContract;

/**
 * Suppress one or more readability rules on the annotated declaration.
 *
 * Applied to a class  → suppresses for all nodes within the class.
 * Applied to a method → suppresses for all nodes within the method.
 * Applied to a property or parameter → suppresses for that node.
 *
 * Pass no arguments to suppress ALL rules.
 *
 * Examples:
 *   #[Suppress]                                   // suppress all rules
 *   #[Suppress(NoLongMethodsRule::class)]          // suppress one rule
 *   #[Suppress(RuleA::class, RuleB::class)]        // suppress multiple
 */
#[\Attribute(\Attribute::TARGET_ALL)]
final class Suppress
{
    /** @var array<int, class-string<RuleContract<\PhpParser\Node>>> */
    private readonly array $rules;

    /**
     * @param class-string<RuleContract<\PhpParser\Node>> ...$rules Leave empty to suppress all rules.
     */
    // @readalizer-suppress NoPublicConstructorRule
    public function __construct(string ...$rules)
    {
        $normalized = [];
        foreach ($rules as $rule) {
            $normalized[] = $rule;
        }
        $this->rules = $normalized;
    }

    /**
     * @param class-string<RuleContract<\PhpParser\Node>> ...$rules Leave empty to suppress all rules.
     */
    public static function create(string ...$rules): self
    {
        return new self(...$rules);
    }

    // @readalizer-suppress NoArrayReturnRule
    /** @return array<int, class-string<RuleContract<\PhpParser\Node>>> */
    public function getRules(): array
    {
        return $this->rules;
    }
}
