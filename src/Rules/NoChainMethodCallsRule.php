<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoChainMethodCallsRule implements RuleContract
{
    public function __construct(private readonly int $maxChain = 5) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([MethodCall::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var MethodCall $node */
        $depth = $this->countChain($node);

        if ($depth <= $this->maxChain) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf('Method call chain depth is %d (max %d).', $depth, $this->maxChain),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function countChain(MethodCall $node): int
    {
        $depth = 1;
        $current = $node->var;
        $seen = [spl_object_id($node) => true];

        while ($current instanceof MethodCall) {
            $id = spl_object_id($current);
            if (isset($seen[$id])) {
                return $depth;
            }
            $seen[$id] = true;

            $depth++;
            if ($depth > $this->maxChain) {
                // We only care whether chain depth exceeds the configured max.
                return $depth;
            }
            $current = $current->var;
        }

        return $depth;
    }
}
