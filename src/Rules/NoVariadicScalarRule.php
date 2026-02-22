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
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Variadic scalar parameters are hard to use safely.
 *
 */
final class NoVariadicScalarRule implements RuleContract
{
    private const RULES_PARAM_NAME = 'rules';
    /** @var string[] */
    private const SCALARS = ['int', 'string', 'bool', 'float'];

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $violation = $this->findViolation($node, $filePath);

        return RuleViolationCollection::create($violation === null ? [] : [$violation]);
    }

    private function isAllowedVariadic(?string $name): bool
    {
        return $name === self::RULES_PARAM_NAME;
    }

    private function findViolation(Node $node, string $filePath): ?RuleViolation
    {
        /** @var ClassMethod|Function_ $node */
        /** @var list<\PhpParser\Node\Param> $params */
        $params = $node->params;
        foreach ($params as $param) {
            if (!$param->variadic || !$param->type instanceof Identifier) {
                continue;
            }

            $type = strtolower($param->type->toString());

            if (!in_array($type, self::SCALARS, true)) {
                continue;
            }

            $paramName = $param->var instanceof \PhpParser\Node\Expr\Variable && is_string($param->var->name)
                ? $param->var->name
                : null;
            if ($this->isAllowedVariadic($paramName)) {
                continue;
            }

            return $this->buildViolation($filePath, $param->getStartLine(), $type);
        }

        return null;
    }

    private function buildViolation(string $filePath, int $line, string $type): RuleViolation
    {
        $message = sprintf(
            'Variadic scalar parameter (%s) detected. Use an array or value object instead.',
            $type,
        );

        return RuleViolation::createFromDetails(
            message:   $message,
            filePath:  $filePath,
            line:      $line,
            ruleClass: self::class,
        );
    }
}
