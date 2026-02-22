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
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Class, interface, and trait names should be PascalCase.
 *
 */
final class ClassNamePascalCaseRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class, Interface_::class, Trait_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Class_|Interface_|Trait_ $node */
        if ($node->name === null) {
            return RuleViolationCollection::create([]);
        }

        if (!$node->name instanceof \PhpParser\Node\Identifier) {
            return RuleViolationCollection::create([]);
        }

        $name = $node->name->toString();

        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $name) === 1) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('Name "%s" should be PascalCase.', $name),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
