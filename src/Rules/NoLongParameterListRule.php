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
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Long parameter lists make code harder to call correctly.
 *
 */
final class NoLongParameterListRule implements RuleContract
{
    use HasMagicMethods;

    public function __construct(private readonly int $maxParams = 4) {}

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        if ($node instanceof ClassMethod && $this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        /** @var ClassMethod|Function_ $node */
        /** @var list<\PhpParser\Node\Param> $params */
        $params = $node->params;
        $count = count($params);

        if (
            $count <= $this->maxParams
            || !$node->name instanceof \PhpParser\Node\Identifier
        ) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: sprintf(
                '%s has %d parameters (max %d). Consider a value object.',
                $this->buildLabel($node, $node->name->toString()),
                $count,
                $this->maxParams
            ),
            filePath: $filePath,
            line: $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function buildLabel(ClassMethod|Function_ $node, string $name): string
    {
        return $node instanceof ClassMethod
            ? sprintf('Method "%s"', $name)
            : sprintf('Function "%s"', $name);
    }
}
