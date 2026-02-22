<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rulesets;

use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Contracts\RulesetContract;
use Millerphp\Readalizer\Rules\NoBreakInFinallyRule;
use Millerphp\Readalizer\Rules\NoDeepBooleanExpressionRule;
use Millerphp\Readalizer\Rules\NoEchoRule;
use Millerphp\Readalizer\Rules\NoElseAfterReturnRule;
use Millerphp\Readalizer\Rules\NoEmptyCatchRule;
use Millerphp\Readalizer\Rules\NoExitRule;
use Millerphp\Readalizer\Rules\NoAssignmentInConditionRule;
use Millerphp\Readalizer\Rules\NoChainMethodCallsRule;
use Millerphp\Readalizer\Rules\NoComplexConditionRule;
use Millerphp\Readalizer\Rules\NoImplicitTernaryRule;
use Millerphp\Readalizer\Rules\NoMagicStringRule;
use Millerphp\Readalizer\Rules\NoNestedTernaryRule;
use Millerphp\Readalizer\Rules\NoSwitchFallthroughRule;
use Millerphp\Readalizer\Rules\NoYodaConditionsRule;

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
