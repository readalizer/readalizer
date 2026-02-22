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
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Avoid else blocks after an early return/throw.
 *
 */
final class NoElseAfterReturnRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([If_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var If_ $node */
        if ($node->else === null) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->endsWithExit($node->stmts)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Avoid else blocks after return/throw. Use early return and outdent.',
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    /** @param Node[] $stmts */
    private function endsWithExit(array $stmts): bool
    {
        $last = end($stmts);

        return $last instanceof Return_
            || ($last instanceof Expression && $last->expr instanceof Throw_)
            || $last instanceof Exit_
            || $last instanceof Break_
            || $last instanceof Continue_;
    }
}
