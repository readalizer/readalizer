<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Contracts;

use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use PhpParser\Node;

interface FileRuleContract
{
    /**
     * @param Node[] $ast The fully-parsed statement list for this file.
     */
    public function processFile(array $ast, string $filePath): RuleViolationCollection;
}
