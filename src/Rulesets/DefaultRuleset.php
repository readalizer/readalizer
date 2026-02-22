<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rulesets;

use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Contracts\RulesetContract;

final class DefaultRuleset implements RulesetContract
{
    public function getRules(): RuleCollection
    {
        $rules = (new FileStructureRuleset())->getRules();
        $rules = $rules->merge((new TypeSafetyRuleset())->getRules());
        $rules = $rules->merge((new ClassDesignRuleset())->getRules());
        $rules = $rules->merge((new MethodDesignRuleset())->getRules());
        $rules = $rules->merge((new NamingRuleset())->getRules());
        return $rules->merge((new ExpressionRuleset())->getRules());
    }
}
