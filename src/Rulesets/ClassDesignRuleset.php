<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rulesets;

use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Contracts\RulesetContract;
use Readalizer\Readalizer\Rules\ClassNamePascalCaseRule;
use Readalizer\Readalizer\Rules\ConstantUppercaseRule;
use Readalizer\Readalizer\Rules\ExceptionSuffixRule;
use Readalizer\Readalizer\Rules\FinalClassRule;
use Readalizer\Readalizer\Rules\MaxClassLengthRule;
use Readalizer\Readalizer\Rules\NoEmptyClassRule;
use Readalizer\Readalizer\Rules\NoEmptyTraitRule;
use Readalizer\Readalizer\Rules\NoGodClassRule;
use Readalizer\Readalizer\Rules\NoInheritanceRule;
use Readalizer\Readalizer\Rules\NoInterfacesOnFinalClassRule;
use Readalizer\Readalizer\Rules\NoMutablePublicPropertiesRule;
use Readalizer\Readalizer\Rules\PreferPropertyPromotionRule;
use Readalizer\Readalizer\Rules\NoProtectedPropertiesRule;
use Readalizer\Readalizer\Rules\NoPublicPropertiesRule;
use Readalizer\Readalizer\Rules\NoPublicConstructorRule;
use Readalizer\Readalizer\Rules\NoStaticPropertyRule;
use Readalizer\Readalizer\Rules\NoStaticMethodsRule;
use Readalizer\Readalizer\Rules\PropertyNameCamelCaseRule;
use Readalizer\Readalizer\Rules\RequireImmutableValueObjectRule;
use Readalizer\Readalizer\Rules\SingleResponsibilityClassRule;

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
