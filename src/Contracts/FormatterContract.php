<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Contracts;

use Millerphp\Readalizer\Analysis\RuleViolationCollection;

interface FormatterContract
{
    public function format(RuleViolationCollection $violations): string;
}
