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
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoReturnNullRule implements RuleContract
{
    use HasMagicMethods;
    private const TYPE_NULL = 'null';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_ $node */
        if ($node instanceof ClassMethod && $this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        if ($node->stmts === null) {
            return RuleViolationCollection::create([]);
        }

        if ($this->hasNullReturnType($node->returnType)) {
            return RuleViolationCollection::create([]);
        }

        /** @var list<Node> $stmts */
        $stmts = $node->stmts;
        $line = $this->findNullReturn($stmts);

        if ($line === null) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Return null detected. Use nullable return types explicitly.',
            filePath:  $filePath,
            line:      $line,
            ruleClass: self::class,
        )]);
    }

    private function hasNullReturnType(?Node $type): bool
    {
        if ($type instanceof NullableType) {
            return true;
        }

        if ($type instanceof UnionType) {
            foreach ($type->types as $part) {
                if ($part instanceof Identifier && strtolower($part->toString()) === self::TYPE_NULL) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param list<Node> $stmts */
    private function findNullReturn(array $stmts): ?int
    {
        $stack = $stmts;

        while ($stack) {
            $stmt = array_pop($stack);
            if ($stmt instanceof Return_ && $this->isNullLiteral($stmt->expr)) {
                return $stmt->getStartLine();
            }

            $stack = $this->pushChildNodes($stmt, $stack);
        }

        return null;
    }

    private function isNullLiteral(?Node $expr): bool
    {
        return $expr instanceof ConstFetch
            && strtolower($expr->name->toString()) === self::TYPE_NULL;
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
