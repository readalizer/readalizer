<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use Readalizer\Readalizer\Rules\Concerns\HasMagicMethods;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Deeply nested control flow is harder to read and reason about.
 *
 * Each level of nesting (if, foreach, for, while, switch, try) adds cognitive
 * load. Prefer early returns, guard clauses, and extracted helper methods to
 * keep nesting shallow.
 *
 */
final class MaxNestingDepthRule implements RuleContract
{
    use HasMagicMethods;
    public function __construct(private readonly int $maxDepth = 3) {}

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

        if (empty($node->stmts)) {
            return RuleViolationCollection::create([]);
        }
        $depth = $this->calculateMaxDepth($node->stmts, 0);
        if ($depth <= $this->maxDepth) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([$this->buildViolation($node, $filePath, $depth)]);
    }

    private function buildViolation(ClassMethod|Function_ $node, string $filePath, int $depth): RuleViolation
    {
        $label = $node instanceof ClassMethod
            ? sprintf('Method "%s"', $node->name)
            : sprintf('Function "%s"', $node->name);
        $message = sprintf(
            '%s has a nesting depth of %d (max %d). Use guard clauses or extract helper methods.',
            $label,
            $depth,
            $this->maxDepth,
        );

        return RuleViolation::createFromDetails(
            message:   $message,
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        );
    }

    /** @param Stmt[] $stmts */
    private function calculateMaxDepth(array $stmts, int $currentDepth): int
    {
        $maxDepth = $currentDepth;

        foreach ($stmts as $stmt) {
            foreach ($this->getNestedBlocks($stmt) as $block) {
                $depth    = $this->calculateMaxDepth($block, $currentDepth + 1);
                $maxDepth = max($maxDepth, $depth);
            }
        }

        return $maxDepth;
    }

    /** @return array<Stmt[]> */
    // @readalizer-suppress NoArrayReturnRule
    private function getNestedBlocks(Stmt $stmt): array
    {
        return match (true) {
            $stmt instanceof Stmt\If_ => [
                $stmt->stmts,
                ...array_map(fn(Stmt\ElseIf_ $e) => $e->stmts, $stmt->elseifs),
                ...($stmt->else !== null ? [$stmt->else->stmts] : []),
            ],
            $stmt instanceof Stmt\For_, $stmt instanceof Stmt\Foreach_,
            $stmt instanceof Stmt\While_, $stmt instanceof Stmt\Do_ => [$stmt->stmts],
            $stmt instanceof Stmt\Switch_ => array_map(fn(Stmt\Case_ $c) => $c->stmts, $stmt->cases),
            $stmt instanceof Stmt\TryCatch => [
                $stmt->stmts,
                ...array_map(fn(Stmt\Catch_ $c) => $c->stmts, $stmt->catches),
                ...($stmt->finally !== null ? [$stmt->finally->stmts] : []),
            ],
            default => [],
        };
    }
}
