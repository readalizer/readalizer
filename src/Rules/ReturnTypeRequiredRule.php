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
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * All methods and functions must declare an explicit return type.
 *
 * Magic methods where PHP itself does not permit a return type (__construct,
 * __destruct, __clone) are exempt.
 */
final class ReturnTypeRequiredRule implements RuleContract
{
    /** @var string[] */
    private const EXEMPT = ['__construct', '__destruct', '__clone'];

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_ $node */
        if ($node->returnType !== null) {
            return RuleViolationCollection::create([]);
        }
        if ($node instanceof ClassMethod && in_array($node->name->toString(), self::EXEMPT, true)) {
            return RuleViolationCollection::create([]);
        }
        $label = $node instanceof ClassMethod
            ? sprintf('Method "%s"', $node->name)
            : sprintf('Function "%s"', $node->name);
        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   "{$label} is missing a return type declaration.",
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
