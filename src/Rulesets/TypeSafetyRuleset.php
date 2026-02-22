<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rulesets;

use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Contracts\RulesetContract;
use Millerphp\Readalizer\Rules\NoArrayReturnRule;
use Millerphp\Readalizer\Rules\NoMixedTypeRule;
use Millerphp\Readalizer\Rules\NoMixedDocblockRule;
use Millerphp\Readalizer\Rules\NoNullableMixedRule;
use Millerphp\Readalizer\Rules\NoUnionWithMixedRule;
use Millerphp\Readalizer\Rules\NoVariadicScalarRule;
use Millerphp\Readalizer\Rules\ParameterTypeRequiredRule;
use Millerphp\Readalizer\Rules\PreferNullableTypeSyntaxRule;
use Millerphp\Readalizer\Rules\RequireIterableValueTypeRule;
use Millerphp\Readalizer\Rules\RequireVoidReturnRule;
use Millerphp\Readalizer\Rules\NoBoolStringComparisonRule;
use Millerphp\Readalizer\Rules\NoImplicitBoolReturnRule;
use Millerphp\Readalizer\Rules\NoImplicitStringCastRule;
use Millerphp\Readalizer\Rules\NoUntypedPropertyRule;
use Millerphp\Readalizer\Rules\ReturnTypeRequiredRule;

final class TypeSafetyRuleset implements RulesetContract
{
    public function getRules(): RuleCollection
    {
        return RuleCollection::create([
            new ReturnTypeRequiredRule(),
            new NoArrayReturnRule(),
            new ParameterTypeRequiredRule(),
            new NoMixedTypeRule(),
            new NoUntypedPropertyRule(),
            new NoMixedDocblockRule(),
            new RequireIterableValueTypeRule(),
            new NoNullableMixedRule(),
            new NoUnionWithMixedRule(),
            new PreferNullableTypeSyntaxRule(),
            new NoVariadicScalarRule(),
            new RequireVoidReturnRule(),
            new NoBoolStringComparisonRule(),
            new NoImplicitBoolReturnRule(),
            new NoImplicitStringCastRule(),
        ]);
    }
}
