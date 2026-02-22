<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rulesets;

use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Contracts\RulesetContract;
use Millerphp\Readalizer\Rules\InterfaceNamingRule;
use Millerphp\Readalizer\Rules\NoAbbreviationRule;
use Millerphp\Readalizer\Rules\NoNegativeBooleanNameRule;
use Millerphp\Readalizer\Rules\NoPluralClassNameRule;
use Millerphp\Readalizer\Rules\NoPrefixHungarianRule;
use Millerphp\Readalizer\Rules\NoSuffixImplRule;
use Millerphp\Readalizer\Rules\NoManagerSuffixRule;
use Millerphp\Readalizer\Rules\TraitNamingRule;

final class NamingRuleset implements RulesetContract
{
    public function getRules(): RuleCollection
    {
        return RuleCollection::create([
            new InterfaceNamingRule(),
            new TraitNamingRule(),
            new NoAbbreviationRule(),
            new NoNegativeBooleanNameRule(),
            new NoPrefixHungarianRule(),
            new NoSuffixImplRule(),
            new NoManagerSuffixRule(),
            new NoPluralClassNameRule(),
        ]);
    }
}
