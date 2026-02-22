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
use PhpParser\Node\Stmt\ClassMethod;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class MaxMethodStatementsRule implements RuleContract
{
    use HasMagicMethods;

    public function __construct(private readonly int $maxStatements = 12) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($this->isMagicMethod($node) || $node->stmts === null) {
            return RuleViolationCollection::create([]);
        }

        $count = count($node->stmts);

        if ($count <= $this->maxStatements) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('Method "%s" has %d statements (max %d).', $node->name, $count, $this->maxStatements),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }
}
