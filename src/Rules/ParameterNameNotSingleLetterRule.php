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
 * Parameters should be descriptive.
 */
final class ParameterNameNotSingleLetterRule implements RuleContract
{
    /** @var string[] */
    private const ALLOWED = ['i', 'j', 'k'];

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $violations = [];

        /** @var ClassMethod|Function_ $node */
        /** @var list<\PhpParser\Node\Param> $params */
        $params = $node->params;
        foreach ($params as $param) {
            if (!$param->var instanceof Variable || !is_string($param->var->name)) {
                continue;
            }

            $name = $param->var->name;

            if (strlen($name) != 1 || in_array($name, self::ALLOWED, true)) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   sprintf('Parameter "%s" should be descriptive (avoid single-letter names).', $name),
                filePath:  $filePath,
                line:      $param->getStartLine(),
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }
}
