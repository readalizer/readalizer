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
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Continue_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Switch cases should end with a break/return/throw/continue.
 *
 */
final class NoSwitchFallthroughRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Switch_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Switch_ $node */
        $violations = [];
        $cases = $node->cases;

        foreach ($cases as $index => $case) {
            if ($index === count($cases) - 1) {
                continue;
            }

            if ($this->endsWithTerminator($case)) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   'Switch case falls through. Add break/return/throw/continue.',
                filePath:  $filePath,
                line:      $case->getStartLine(),
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }

    private function endsWithTerminator(Case_ $case): bool
    {
        $last = end($case->stmts);

        return $last instanceof Break_
            || $last instanceof Return_
            || ($last instanceof Expression && $last->expr instanceof Throw_)
            || $last instanceof Continue_;
    }
}
