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
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\DNumber;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Magic numbers obscure intent.
 */
final class NoMagicNumberRule implements RuleContract
{
    /** @var float[] */
    private const ALLOWED = [-1.0, 0.0, 1.0];

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([LNumber::class, DNumber::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $value = $node instanceof LNumber ? (float) $node->value : $node->value;

        if (in_array($value, self::ALLOWED, true)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Magic number detected. Use a named constant instead.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
