<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rulesets;

use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Contracts\RulesetContract;
use Readalizer\Readalizer\Rules\NoArrayReturnRule;
use Readalizer\Readalizer\Rules\NoMixedTypeRule;
use Readalizer\Readalizer\Rules\NoMixedDocblockRule;
use Readalizer\Readalizer\Rules\NoNullableMixedRule;
use Readalizer\Readalizer\Rules\NoUnionWithMixedRule;
use Readalizer\Readalizer\Rules\NoVariadicScalarRule;
use Readalizer\Readalizer\Rules\ParameterTypeRequiredRule;
use Readalizer\Readalizer\Rules\PreferNullableTypeSyntaxRule;
use Readalizer\Readalizer\Rules\RequireIterableValueTypeRule;
use Readalizer\Readalizer\Rules\RequireVoidReturnRule;
use Readalizer\Readalizer\Rules\NoBoolStringComparisonRule;
use Readalizer\Readalizer\Rules\NoImplicitBoolReturnRule;
use Readalizer\Readalizer\Rules\NoImplicitStringCastRule;
use Readalizer\Readalizer\Rules\NoUntypedPropertyRule;
use Readalizer\Readalizer\Rules\ReturnTypeRequiredRule;

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
