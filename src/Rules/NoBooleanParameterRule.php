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
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoBooleanParameterRule implements RuleContract
{
    private const TYPE_BOOL = 'bool';
    private const VALUE_TRUE = 'true';
    private const VALUE_FALSE = 'false';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_ $node */
        /** @var list<\PhpParser\Node\Param> $params */
        $params = $node->params;
        foreach ($params as $param) {
            if ($param->type instanceof Identifier && strtolower($param->type->toString()) === self::TYPE_BOOL) {
                return RuleViolationCollection::create([RuleViolation::createFromDetails(
                    message:   'Boolean parameters are discouraged. Use separate methods.',
                    filePath:  $filePath,
                    line:      $param->getStartLine(),
                    ruleClass: self::class,
                )]);
            }
            if ($param->default instanceof ConstFetch) {
                $name = strtolower($param->default->name->toString());
                if ($name === self::VALUE_TRUE || $name === self::VALUE_FALSE) {
                    return RuleViolationCollection::create([RuleViolation::createFromDetails(
                        message:   'Boolean parameters are discouraged. Use separate methods.',
                        filePath:  $filePath,
                        line:      $param->getStartLine(),
                        ruleClass: self::class,
                    )]);
                }
            }
        }

        return RuleViolationCollection::create([]);
    }
}
