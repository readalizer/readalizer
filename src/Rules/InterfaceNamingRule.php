<?php

/**
 * Interfaces should use the "Contract" suffix to make explicit that they define
 * a contract between components (e.g. ClientContract, RendererContract).
 *
 * This makes it immediately clear at the call-site that you are programming
 * to an abstraction, not a concrete implementation.
 *
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class InterfaceNamingRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Interface_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Interface_ $node */
        if ($node->name === null) {
            return RuleViolationCollection::create([]);
        }

        $name = $node->name->toString();

        if (str_ends_with($name, 'Contract')) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([
            RuleViolation::createFromDetails(
                message: sprintf(
                    'Interface "%s" should use the "Contract" suffix (e.g. "%sContract").',
                    $name,
                    $name
                ),
                filePath: $filePath,
                line: $node->getStartLine(),
                ruleClass: self::class,
            ),
        ]);
    }
}
