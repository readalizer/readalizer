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
use PhpParser\Node\Stmt\Class_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoInterfacesOnFinalClassRule implements RuleContract
{
    public function __construct(private readonly int $maxInterfaces = 1) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        if (!$node->isFinal()) {
            return RuleViolationCollection::create([]);
        }

        if (count($node->implements) <= $this->maxInterfaces) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf(
                'Final class implements %d interfaces (max %d).',
                count($node->implements),
                $this->maxInterfaces
            ),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
