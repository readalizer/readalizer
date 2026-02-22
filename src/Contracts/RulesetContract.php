<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Contracts;

use Millerphp\Readalizer\Analysis\RuleCollection;

interface RulesetContract
{
    public function getRules(): RuleCollection;
}
