<?php

/**
 * Expands a list of RulesetContract instances into a flat RuleCollection.
 *
 * This utility class processes configured rulesets, retrieving all rules
 * defined within them and consolidating them into a single list for the analyser.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Config;

use Millerphp\Readalizer\Analysis\RuleCollection;
use Millerphp\Readalizer\Contracts\RulesetContract;

final class RulesetExpander
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<int, RulesetContract> $rulesets
     */
    public function buildRules(array $rulesets): RuleCollection
    {
        $rules = RuleCollection::create([]);
        foreach ($rulesets as $ruleset) {
            $rules = $rules->merge($ruleset->getRules());
        }
        return $rules;
    }
}
