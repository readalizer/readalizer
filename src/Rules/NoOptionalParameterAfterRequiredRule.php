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
use PhpParser\Node\Stmt\Function_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoOptionalParameterAfterRequiredRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $foundOptional = false;

        /** @var ClassMethod|Function_ $node */
        /** @var list<\PhpParser\Node\Param> $params */
        $params = $node->params;
        foreach ($params as $param) {
            if ($param->default !== null) {
                $foundOptional = true;
                continue;
            }
            if ($foundOptional) {
                return RuleViolationCollection::create([RuleViolation::createFromDetails(
                    message:   'Required parameters must not follow optional parameters.',
                    filePath:  $filePath,
                    line:      $param->getStartLine(),
                    ruleClass: self::class,
                )]);
            }
        }

        return RuleViolationCollection::create([]);
    }
}
