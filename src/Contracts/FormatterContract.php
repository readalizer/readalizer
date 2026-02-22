<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Contracts;

use Readalizer\Readalizer\Analysis\RuleViolationCollection;

interface FormatterContract
{
    public function format(RuleViolationCollection $violations): string;
}
