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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Avoid type prefixes in names (Hungarian notation).
 *
 */
final class NoHungarianNotationRule implements RuleContract
{
    /** @var string[] */
    private const PREFIXES = ['str', 'int', 'bool', 'arr', 'obj', 'fn', 'cb', 'num', 'dt', 'cfg'];

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class, Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_|Property $node */
        $names = $this->extractNames($node);
        $violations = [];

        foreach ($names as $name => $line) {
            if (!$this->looksHungarian($name)) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   sprintf('Name "%s" looks like Hungarian notation. Use descriptive words instead.', $name),
                filePath:  $filePath,
                line:      $line,
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }

    /**
     * @param ClassMethod|Function_|Property $node
     * @return array<string, int>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function extractNames(Node $node): array
    {
        if ($node instanceof Property) {
            $names = [];
            /** @var list<\PhpParser\Node\Stmt\PropertyProperty> $props */
            $props = $node->props;
            foreach ($props as $prop) {
                $names[$prop->name->toString()] = $prop->getStartLine();
            }
            return $names;
        }

        $names = [];
        /** @var list<\PhpParser\Node\Param> $params */
        $params = $node->params;
        foreach ($params as $param) {
            if ($param->var instanceof Variable && is_string($param->var->name)) {
                $names[$param->var->name] = $param->getStartLine();
            }
        }

        return $names;
    }

    private function looksHungarian(string $name): bool
    {
        foreach (self::PREFIXES as $prefix) {
            if (preg_match('/^' . $prefix . '[A-Z]/', $name) === 1) {
                return true;
            }
        }

        return false;
    }
}
