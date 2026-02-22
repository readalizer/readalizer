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
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Getter methods should return a value.
 */
final class GetterMustReturnValueRule implements RuleContract
{
    use HasMagicMethods;
    private const TYPE_VOID = 'void';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        $name = strtolower($node->name->toString());

        if (!str_starts_with($name, 'get')) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->returnsVoid($node)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('Getter "%s" should return a value, not void.', $node->name),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function returnsVoid(ClassMethod $node): bool
    {
        return $node->returnType instanceof Identifier
            && strtolower($node->returnType->toString()) === self::TYPE_VOID;
    }
}
