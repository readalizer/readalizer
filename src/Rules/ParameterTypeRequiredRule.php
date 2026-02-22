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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Every function and method parameter must carry an explicit type declaration.
 *
 */
final class ParameterTypeRequiredRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_ $node */
        $violations = [];

        foreach ($node->params as $param) {
            if ($param->type !== null) {
                continue;
            }
            $name = ($param->var instanceof Variable && is_string($param->var->name))
                ? '$' . $param->var->name : '$?';
            $violations[] = RuleViolation::createFromDetails(
                message:   "Parameter {$name} is missing a type declaration.",
                filePath:  $filePath,
                line:      $param->getStartLine(),
                ruleClass: self::class,
            );
        }
        return RuleViolationCollection::create($violations);
    }
}
