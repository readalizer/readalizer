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
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Const_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Constants should be uppercase with underscores.
 *
 */
final class ConstantUppercaseRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassConst::class, Const_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassConst|Const_ $node */
        $names = $this->extractNames($node);
        $violations = [];

        foreach ($names as $name => $line) {
            if (preg_match('/^[A-Z][A-Z0-9_]*$/', $name) === 1) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   sprintf('Constant "%s" should be uppercase with underscores.', $name),
                filePath:  $filePath,
                line:      $line,
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }

    /**
     * @param ClassConst|Const_ $node
     * @return array<string, int>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function extractNames(Node $node): array
    {
        $names = [];

        /** @var list<\PhpParser\Node\Const_> $consts */
        $consts = $node->consts;
        foreach ($consts as $const) {
            $names[$const->name->toString()] = $const->getStartLine();
        }

        return $names;
    }
}
