<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Analysis\NodeTypeCollection;
use Millerphp\Readalizer\Contracts\RuleContract;
use Millerphp\Readalizer\Rules\Concerns\HasMagicMethods;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoImplicitBoolReturnRule implements RuleContract
{
    use HasMagicMethods;
    private const TYPE_BOOL = 'bool';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        if ($node instanceof ClassMethod && $this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->hasBoolReturnType($node)) {
            return RuleViolationCollection::create([]);
        }

        if ($node->stmts === null) {
            return RuleViolationCollection::create([]);
        }

        /** @var list<Node> $stmts */
        $stmts = $node->stmts;
        $line = $this->findNonLiteralReturn($stmts);

        if ($line === null) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Boolean methods should return true/false explicitly.',
            filePath:  $filePath,
            line:      $line,
            ruleClass: self::class,
        )]);
    }

    private function hasBoolReturnType(Node $node): bool
    {
        return $node->returnType instanceof Identifier
            && strtolower($node->returnType->toString()) === self::TYPE_BOOL;
    }

    /** @param list<Node> $stmts */
    private function findNonLiteralReturn(array $stmts): ?int
    {
        $stack = $stmts;

        while ($stack) {
            $stmt = array_pop($stack);
            if ($stmt instanceof Return_ && !$this->isAllowedReturn($stmt->expr)) {
                return $stmt->getStartLine();
            }

            $stack = $this->pushChildNodes($stmt, $stack);
        }

        return null;
    }

    private function isAllowedReturn(?Node $expr): bool
    {
        if ($expr instanceof ConstFetch) {
            return in_array(strtolower($expr->name->toString()), ['true', 'false'], true);
        }

        return $expr instanceof Node\Expr;
    }

    /**
     * @param array<int|string, Node> $stack
     * @return array<int|string, Node>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function pushChildNodes(Node $node, array $stack): array
    {
        foreach ($node->getSubNodeNames() as $name) {
            $child = $node->$name;
            if ($child instanceof Node) {
                $stack[] = $child;
                continue;
            }
            if (is_array($child)) {
                $stack = $this->pushArrayNodes($child, $stack);
            }
        }

        return $stack;
    }

    /**
     * @param array<mixed, mixed> $nodes
     * @param array<int|string, Node> $stack
     * @return array<int|string, Node>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function pushArrayNodes(array $nodes, array $stack): array
    {
        foreach ($nodes as $sub) {
            if ($sub instanceof Node) {
                $stack[] = $sub;
            }
        }

        return $stack;
    }
}
