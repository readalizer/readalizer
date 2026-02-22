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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoPrefixHungarianRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class, Property::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_|Property $node */
        $names = $this->collectNames($node);

        foreach ($names as $name => $line) {
            if ($this->isHungarianPrefix($name)) {
                return RuleViolationCollection::create([RuleViolation::createFromDetails(
                    message:   sprintf('Name "%s" uses Hungarian-style prefix.', $name),
                    filePath:  $filePath,
                    line:      $line,
                    ruleClass: self::class,
                )]);
            }
        }

        return RuleViolationCollection::create([]);
    }

    /**
     * @param ClassMethod|Function_|Property $node
     * @return array<string,int>
     */
    // @readalizer-suppress NoArrayReturnRule
    private function collectNames(Node $node): array
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

    private function isHungarianPrefix(string $name): bool
    {
        return preg_match('/^(str|int|bool|arr|obj|num|cfg|ctx)_/i', $name) === 1;
    }
}
