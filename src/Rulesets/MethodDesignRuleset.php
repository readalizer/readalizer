<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rulesets;

use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Contracts\RulesetContract;
use Readalizer\Readalizer\Rules\BooleanMethodPrefixRule;
use Readalizer\Readalizer\Rules\FunctionVerbNameRule;
use Readalizer\Readalizer\Rules\GetterMustReturnValueRule;
use Readalizer\Readalizer\Rules\MaxNestingDepthRule;
use Readalizer\Readalizer\Rules\MaxMethodStatementsRule;
use Readalizer\Readalizer\Rules\NoBooleanParameterRule;
use Readalizer\Readalizer\Rules\NoConstructorWorkRule;
use Readalizer\Readalizer\Rules\NoDefaultArrayParameterRule;
use Readalizer\Readalizer\Rules\NoEmptyMethodRule;
use Readalizer\Readalizer\Rules\NoLongParameterListRule;
use Readalizer\Readalizer\Rules\NoHungarianNotationRule;
use Readalizer\Readalizer\Rules\NoLongMethodsRule;
use Readalizer\Readalizer\Rules\NoNestedLoopsRule;
use Readalizer\Readalizer\Rules\NoNestedTryRule;
use Readalizer\Readalizer\Rules\NoOptionalParameterAfterRequiredRule;
use Readalizer\Readalizer\Rules\NoReferenceParameterRule;
use Readalizer\Readalizer\Rules\NoReturnNullRule;
use Readalizer\Readalizer\Rules\NoThrowGenericExceptionRule;
use Readalizer\Readalizer\Rules\NoCatchGenericExceptionRule;
use Readalizer\Readalizer\Rules\ParameterNameNotSingleLetterRule;
use Readalizer\Readalizer\Rules\RequireNamedConstructorRule;

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
