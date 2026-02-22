<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rulesets;

use Readalizer\Readalizer\Analysis\RuleCollection;
use Readalizer\Readalizer\Contracts\RulesetContract;
use Readalizer\Readalizer\Rules\InterfaceNamingRule;
use Readalizer\Readalizer\Rules\NoAbbreviationRule;
use Readalizer\Readalizer\Rules\NoNegativeBooleanNameRule;
use Readalizer\Readalizer\Rules\NoPluralClassNameRule;
use Readalizer\Readalizer\Rules\NoPrefixHungarianRule;
use Readalizer\Readalizer\Rules\NoSuffixImplRule;
use Readalizer\Readalizer\Rules\NoManagerSuffixRule;
use Readalizer\Readalizer\Rules\TraitNamingRule;

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
