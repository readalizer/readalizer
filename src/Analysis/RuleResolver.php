<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use Readalizer\Readalizer\Contracts\RuleContract;
use PhpParser\Node;

final class RuleResolver
{
    /** @var array<class-string<Node>, array<int, RuleContract<\PhpParser\Node>>> */
    private array $rulesByNodeClass = [];

    private function __construct(private readonly NodeRuleCollection $rules)
    {
    }

    public static function create(NodeRuleCollection $rules): self
    {
        return new self($rules);
    }

    /**
     * @return array<int, RuleContract<\PhpParser\Node>> // @readalizer-suppress NoArrayReturnRule
     */
    public function getRulesForNode(Node $node): array // @readalizer-suppress NoArrayReturnRule
    {
        $nodeClass = $node::class;
        if (array_key_exists($nodeClass, $this->rulesByNodeClass)) {
            return $this->rulesByNodeClass[$nodeClass];
        }

        $rules = [];
        foreach ($this->rules as $rule) {
            foreach ($rule->getNodeTypes() as $nodeType) {
                if ($node instanceof $nodeType) {
                    $rules[] = $rule;
                    break;
                }
            }
        }

        $this->rulesByNodeClass[$nodeClass] = $rules;

        return $rules;
    }
}
