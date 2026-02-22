<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Contracts;

use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use PhpParser\Node;

/**
 * @template TNode of Node = Node
 */
interface RuleContract
{
    /**
     * The PhpParser\Node class names this rule wants to inspect.
     *
     * @return NodeTypeCollection<Node>
     */
    public function getNodeTypes(): NodeTypeCollection;

    /**
     * Inspect a matching node and return any violations found.
     *
     * @param TNode $node
     */
    public function processNode(Node $node, string $filePath): RuleViolationCollection;
}
