<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Analysis\NodeTypeCollection;
use Millerphp\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoImplicitStringCastRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([String_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'String cast detected. Prefer explicit formatting.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
