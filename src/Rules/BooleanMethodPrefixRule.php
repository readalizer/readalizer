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
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Boolean-returning methods should read like predicates.
 *
 */
final class BooleanMethodPrefixRule implements RuleContract
{
    use HasMagicMethods;
    private const TYPE_BOOL = 'bool';
    /** @var string[] */
    private const PREFIXES = [
        'is', 'has', 'can', 'should', 'matches', 'contains', 'allows',
        'supports', 'needs', 'uses', 'starts', 'ends', 'detects', 'detect',
        'returns', 'looks', 'does',
    ];

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

        if (!$this->returnsBool($node)) {
            return RuleViolationCollection::create([]);
        }

        if (!$node->name instanceof Identifier) {
            return RuleViolationCollection::create([]);
        }

        $nameValue = $node->name->toString();
        $name = strtolower($nameValue);

        if ($this->hasAllowedPrefix($name)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('Boolean method "%s" should start with is/has/can/should.', $nameValue),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function returnsBool(Node $node): bool
    {
        if ($node->returnType instanceof Identifier) {
            return strtolower($node->returnType->toString()) === self::TYPE_BOOL;
        }

        return false;
    }

    private function hasAllowedPrefix(string $name): bool
    {
        foreach (self::PREFIXES as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
