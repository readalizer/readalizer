<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

final class NodeScopeStack
{
    /** @var Stmt\ClassLike[] */
    private array $classStack = [];

    /** @var array<Node> ClassMethod|Function_|Closure|ArrowFunction */
    private array $methodStack = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function push(Node $node): void
    {
        if ($node instanceof Stmt\ClassLike) {
            $this->classStack[] = $node;
            return;
        }

        if ($this->isCallable($node)) {
            $this->methodStack[] = $node;
        }
    }

    public function pop(Node $node): void
    {
        if ($node instanceof Stmt\ClassLike) {
            array_pop($this->classStack);
            return;
        }

        if ($this->isCallable($node)) {
            array_pop($this->methodStack);
        }
    }

    public function getCurrentMethod(): ?Node
    {
        return empty($this->methodStack) ? null : end($this->methodStack);
    }

    public function getCurrentClass(): ?Node
    {
        return empty($this->classStack) ? null : end($this->classStack);
    }

    private function isCallable(Node $node): bool
    {
        return $node instanceof Stmt\ClassMethod
            || $node instanceof Stmt\Function_
            || $node instanceof Expr\Closure
            || $node instanceof Expr\ArrowFunction;
    }
}
