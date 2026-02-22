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
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Deeply nested loops are hard to follow.
 *
 */
final class NoNestedLoopsRule implements RuleContract
{
    use HasMagicMethods;
    public function __construct(private readonly int $maxDepth = 2) {}

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

        $depth = $this->maxLoopDepth($node->stmts, 0);

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

        return RuleViolation::createFromDetails(
            message:   sprintf('%s has loop nesting depth %d (max %d).', $label, $depth, $this->maxDepth),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        );
    }

    /** @param Stmt[] $stmts */
    private function maxLoopDepth(array $stmts, int $current): int
    {
        $max = $current;

        foreach ($stmts as $stmt) {
            foreach ($this->collectLoopBlocks($stmt) as $block) {
                $depth = $this->maxLoopDepth($block, $current + 1);
                $max = max($max, $depth);
            }
        }

        return $max;
    }

    /** @return array<Stmt[]> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectLoopBlocks(Stmt $stmt): array
    {
        return match (true) {
            $stmt instanceof Stmt\For_, $stmt instanceof Stmt\Foreach_,
            $stmt instanceof Stmt\While_, $stmt instanceof Stmt\Do_ => [$stmt->stmts],
            default => [],
        };
    }
}
