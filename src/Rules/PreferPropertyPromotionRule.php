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
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Constructor property promotion should be preferred over explicit property declaration.
 *
 */
final class PreferPropertyPromotionRule implements RuleContract
{
    private const CONSTRUCTOR_NAME = '__construct';
    private const THIS_VAR = 'this';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($node->name->toString() !== self::CONSTRUCTOR_NAME) {
            return RuleViolationCollection::create([]);
        }

        if ($node->stmts === null) {
            return RuleViolationCollection::create([]);
        }

        $params = $this->collectPromotableParams($node);

        if (empty($params)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create(
            $this->collectViolations($node->stmts, $filePath, $params)
        );
    }

    /** @param array<string, Node\Param> $params */
    private function isPromotionCandidate(Assign $assign, array $params): bool
    {
        $propertyName = $this->getAssignedPropertyName($assign);
        if ($propertyName === null) {
            return false;
        }

        $paramName = $this->getAssignedParamName($assign);
        if ($paramName === null) {
            return false;
        }

        if (!isset($params[$paramName])) {
            return false;
        }

        return $propertyName === $paramName;
    }

    /** @return array<string, Node\Param> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectPromotableParams(ClassMethod $node): array
    {
        $params = [];
        foreach ($node->params as $param) {
            // If it already has flags, it's already promoted. Variadic params can't be promoted.
            if ($param->flags !== 0 || $param->variadic) {
                continue;
            }

            if ($param->var instanceof Variable && is_string($param->var->name)) {
                $params[$param->var->name] = $param;
            }
        }

        return $params;
    }

    /**
     * @param list<Stmt> $stmts
     * @param array<string, Node\Param> $params
     * @return RuleViolation[]
     */
    // @readalizer-suppress NoArrayReturnRule
    private function collectViolations(array $stmts, string $filePath, array $params): array
    {
        $violations = [];

        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
                continue;
            }

            $assign = $stmt->expr;
            if (!$this->isPromotionCandidate($assign, $params)) {
                continue;
            }

            $propertyName = $this->getAssignedPropertyName($assign);
            if ($propertyName === null) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   sprintf('Property "%s" should use constructor property promotion.', $propertyName),
                filePath:  $filePath,
                line:      $stmt->getStartLine(),
                ruleClass: self::class,
            );
        }

        return $violations;
    }

    private function getAssignedPropertyName(Assign $assign): ?string
    {
        if (!$assign->var instanceof PropertyFetch) {
            return null;
        }

        if (!$assign->var->var instanceof Variable) {
            return null;
        }

        if ($assign->var->var->name !== self::THIS_VAR) {
            return null;
        }

        if (!$assign->var->name instanceof Identifier) {
            return null;
        }

        return $assign->var->name->toString();
    }

    private function getAssignedParamName(Assign $assign): ?string
    {
        if (!$assign->expr instanceof Variable) {
            return null;
        }

        if (!is_string($assign->expr->name)) {
            return null;
        }

        return $assign->expr->name;
    }
}
