<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rulesets;

use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Contracts\RulesetContract;
use Millerphp\Readalizer\Rules\ClassNamePascalCaseRule;
use Millerphp\Readalizer\Rules\ConstantUppercaseRule;
use Millerphp\Readalizer\Rules\ExceptionSuffixRule;
use Millerphp\Readalizer\Rules\FinalClassRule;
use Millerphp\Readalizer\Rules\MaxClassLengthRule;
use Millerphp\Readalizer\Rules\NoEmptyClassRule;
use Millerphp\Readalizer\Rules\NoEmptyTraitRule;
use Millerphp\Readalizer\Rules\NoGodClassRule;
use Millerphp\Readalizer\Rules\NoInheritanceRule;
use Millerphp\Readalizer\Rules\NoInterfacesOnFinalClassRule;
use Millerphp\Readalizer\Rules\NoMutablePublicPropertiesRule;
use Millerphp\Readalizer\Rules\PreferPropertyPromotionRule;
use Millerphp\Readalizer\Rules\NoProtectedPropertiesRule;
use Millerphp\Readalizer\Rules\NoPublicPropertiesRule;
use Millerphp\Readalizer\Rules\NoPublicConstructorRule;
use Millerphp\Readalizer\Rules\NoStaticPropertyRule;
use Millerphp\Readalizer\Rules\NoStaticMethodsRule;
use Millerphp\Readalizer\Rules\PropertyNameCamelCaseRule;
use Millerphp\Readalizer\Rules\RequireImmutableValueObjectRule;
use Millerphp\Readalizer\Rules\SingleResponsibilityClassRule;

final class ClassDesignRuleset implements RulesetContract
{
    public function getRules(): RuleCollection
    {
        return RuleCollection::create([
            new ClassNamePascalCaseRule(),
            new FinalClassRule(),
            new NoPublicPropertiesRule(),
            new NoMutablePublicPropertiesRule(),
            new NoStaticPropertyRule(),
            new NoProtectedPropertiesRule(),
            new NoPublicConstructorRule(),
            new PreferPropertyPromotionRule(),
            new NoStaticMethodsRule(),
            new PropertyNameCamelCaseRule(),
            new ConstantUppercaseRule(),
            new ExceptionSuffixRule(),
            new MaxClassLengthRule(),
            new NoGodClassRule(maxMethods: 10, maxProperties: 10),
            new NoEmptyClassRule(),
            new NoEmptyTraitRule(),
            new SingleResponsibilityClassRule(maxPublicMethods: 8),
            new RequireImmutableValueObjectRule(),
            new NoInheritanceRule(),
            new NoInterfacesOnFinalClassRule(maxInterfaces: 1),
        ]);
    }
}
