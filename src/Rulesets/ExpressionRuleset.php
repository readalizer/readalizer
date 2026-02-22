<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rulesets;

use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Contracts\RulesetContract;
use Readalizer\Readalizer\Rules\NoBreakInFinallyRule;
use Readalizer\Readalizer\Rules\NoDeepBooleanExpressionRule;
use Readalizer\Readalizer\Rules\NoEchoRule;
use Readalizer\Readalizer\Rules\NoElseAfterReturnRule;
use Readalizer\Readalizer\Rules\NoEmptyCatchRule;
use Readalizer\Readalizer\Rules\NoExitRule;
use Readalizer\Readalizer\Rules\NoAssignmentInConditionRule;
use Readalizer\Readalizer\Rules\NoChainMethodCallsRule;
use Readalizer\Readalizer\Rules\NoComplexConditionRule;
use Readalizer\Readalizer\Rules\NoImplicitTernaryRule;
use Readalizer\Readalizer\Rules\NoMagicStringRule;
use Readalizer\Readalizer\Rules\NoNestedTernaryRule;
use Readalizer\Readalizer\Rules\NoSwitchFallthroughRule;
use Readalizer\Readalizer\Rules\NoYodaConditionsRule;

final class ExpressionRuleset implements RulesetContract
{
    public function getRules(): RuleCollection
    {
        return RuleCollection::create([
            new NoNestedTernaryRule(),
            new NoDeepBooleanExpressionRule(maxConditions: 3),
            new NoElseAfterReturnRule(),
            new NoEmptyCatchRule(),
            new NoSwitchFallthroughRule(),
            new NoBreakInFinallyRule(),
            new NoEchoRule(),
            new NoExitRule(),
            new NoYodaConditionsRule(),
            new NoImplicitTernaryRule(),
            new NoComplexConditionRule(),
            new NoChainMethodCallsRule(maxChain: 5),
            new NoAssignmentInConditionRule(),
            new NoMagicStringRule(),
        ]);
    }
}
