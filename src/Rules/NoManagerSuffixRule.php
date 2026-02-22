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
use PhpParser\Node\Stmt\Class_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoManagerSuffixRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_ $node */
        if ($node->name === null) {
            return RuleViolationCollection::create([]);
        }

        $name = $node->name->toString();

        if (!$this->hasBadSuffix($name)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('Class "%s" uses a discouraged suffix (Manager/Helper/Util).', $name),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function hasBadSuffix(string $name): bool
    {
        return str_ends_with($name, 'Manager')
            || str_ends_with($name, 'Helper')
            || str_ends_with($name, 'Util');
    }
}
