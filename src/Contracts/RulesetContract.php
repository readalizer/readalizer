<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Contracts;

use Readalizer\Readalizer\Analysis\RuleCollection;

interface RulesetContract
{
    public function getRules(): RuleCollection;
}
