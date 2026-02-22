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
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Returning raw arrays is discouraged. Prefer specific objects or value objects for better type safety and clarity.
 */
final class NoArrayReturnRule implements RuleContract
{
    private const TYPE_ARRAY = 'array';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_ $node */
        $returnType = $node->returnType;

        if ($returnType === null) {
            return RuleViolationCollection::create([]);
        }

        // Handle both "array" and "?array"
        $typeToCheck = $returnType instanceof NullableType ? $returnType->type : $returnType;

        if ($typeToCheck instanceof Identifier && $typeToCheck->toLowerString() === self::TYPE_ARRAY) {
            return RuleViolationCollection::create([RuleViolation::createFromDetails(
                message: 'Returning "array" is discouraged. Use a specific Class or Value Object for type safety.',
                filePath: $filePath,
                line: $node->getStartLine(),
                ruleClass: self::class,
            )]);
        }

        return RuleViolationCollection::create([]);
    }
}
