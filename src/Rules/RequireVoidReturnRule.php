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
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class RequireVoidReturnRule implements RuleContract
{
    use HasMagicMethods;

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        if ($node instanceof ClassMethod && $this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        if ($node->returnType !== null) {
            return RuleViolationCollection::create([]);
        }

        if ($node->stmts === null) {
            return RuleViolationCollection::create([]);
        }

        /** @var list<Node> $stmts */
        $stmts = $node->stmts;
        if ($this->hasReturnWithValue($stmts)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Method/function has no return statements and should declare void return type.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    /** @param array<mixed, mixed> $stmts */
    private function hasReturnWithValue(array $stmts): bool
    {
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Node) {
                continue;
            }
            if ($stmt instanceof Return_ && $stmt->expr !== null) {
                return true;
            }
            foreach ($stmt->getSubNodeNames() as $name) {
                $child = $stmt->$name;
                if ($child instanceof Node && $this->hasReturnWithValue([$child])) {
                    return true;
                }
                if (is_array($child) && $this->hasReturnWithValue($child)) {
                    return true;
                }
            }
        }

        return false;
    }
}
