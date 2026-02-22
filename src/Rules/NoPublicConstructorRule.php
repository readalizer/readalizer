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
use PhpParser\Node\Stmt\ClassMethod;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoPublicConstructorRule implements RuleContract
{
    private const CONSTRUCTOR_NAME = '__construct';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($this->isInternalRuleFile($filePath)) {
            return RuleViolationCollection::create([]);
        }

        if ($node->name->toString() !== self::CONSTRUCTOR_NAME) {
            return RuleViolationCollection::create([]);
        }

        if (!$node->isPublic()) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Public constructors are discouraged. Prefer named constructors or factories.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function isInternalRuleFile(string $filePath): bool
    {
        return str_contains($filePath, DIRECTORY_SEPARATOR . 'Rules' . DIRECTORY_SEPARATOR)
            || str_contains($filePath, DIRECTORY_SEPARATOR . 'Rulesets' . DIRECTORY_SEPARATOR);
    }
}
