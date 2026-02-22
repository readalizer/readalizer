<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rulesets;

use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Contracts\RulesetContract;
use Millerphp\Readalizer\Rules\BooleanMethodPrefixRule;
use Millerphp\Readalizer\Rules\FunctionVerbNameRule;
use Millerphp\Readalizer\Rules\GetterMustReturnValueRule;
use Millerphp\Readalizer\Rules\MaxNestingDepthRule;
use Millerphp\Readalizer\Rules\MaxMethodStatementsRule;
use Millerphp\Readalizer\Rules\NoBooleanParameterRule;
use Millerphp\Readalizer\Rules\NoConstructorWorkRule;
use Millerphp\Readalizer\Rules\NoDefaultArrayParameterRule;
use Millerphp\Readalizer\Rules\NoEmptyMethodRule;
use Millerphp\Readalizer\Rules\NoLongParameterListRule;
use Millerphp\Readalizer\Rules\NoHungarianNotationRule;
use Millerphp\Readalizer\Rules\NoLongMethodsRule;
use Millerphp\Readalizer\Rules\NoNestedLoopsRule;
use Millerphp\Readalizer\Rules\NoNestedTryRule;
use Millerphp\Readalizer\Rules\NoOptionalParameterAfterRequiredRule;
use Millerphp\Readalizer\Rules\NoReferenceParameterRule;
use Millerphp\Readalizer\Rules\NoReturnNullRule;
use Millerphp\Readalizer\Rules\NoThrowGenericExceptionRule;
use Millerphp\Readalizer\Rules\NoCatchGenericExceptionRule;
use Millerphp\Readalizer\Rules\ParameterNameNotSingleLetterRule;
use Millerphp\Readalizer\Rules\RequireNamedConstructorRule;

final class MethodDesignRuleset implements RulesetContract
{
    public function getRules(): RuleCollection
    {
        return RuleCollection::create([
            new FunctionVerbNameRule(),
            new BooleanMethodPrefixRule(),
            new GetterMustReturnValueRule(),
            new ParameterNameNotSingleLetterRule(),
            new NoHungarianNotationRule(),
            new NoLongMethodsRule(maxLines: 30),
            new NoEmptyMethodRule(),
            new NoLongParameterListRule(maxParams: 4),
            new NoConstructorWorkRule(maxLines: 10),
            new MaxNestingDepthRule(maxDepth: 3),
            new NoNestedLoopsRule(maxDepth: 2),
            new MaxMethodStatementsRule(maxStatements: 12),
            new NoBooleanParameterRule(),
            new NoOptionalParameterAfterRequiredRule(),
            new NoDefaultArrayParameterRule(),
            new NoReferenceParameterRule(),
            new NoReturnNullRule(),
            new NoThrowGenericExceptionRule(),
            new NoCatchGenericExceptionRule(),
            new NoNestedTryRule(),
            new RequireNamedConstructorRule(),
        ]);
    }
}
