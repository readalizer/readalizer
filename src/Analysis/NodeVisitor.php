<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use PhpParser\Node;

final class NodeVisitor implements \PhpParser\NodeVisitor
{
    private RuleViolationCollection $violations;

    private function __construct(
        private readonly string $filePath,
        private readonly SuppressionChecker $checker,
        private readonly RuleResolver $ruleResolver,
        private readonly NodeScopeStack $scopeStack,
        private readonly DebugLogger $debugLogger,
    ) {
        $this->violations = RuleViolationCollection::create([]);
    }

    public static function create(
        NodeRuleCollection $rules,
        string $filePath,
        SuppressionChecker $checker,
        bool $debug = false, // @readalizer-suppress NoBooleanParameterRule
    ): self {
        $debugLogger = $debug
            ? DebugLogger::createEnabled($filePath)
            : DebugLogger::createDisabled($filePath);

        return new self(
            $filePath,
            $checker,
            RuleResolver::create($rules),
            NodeScopeStack::create(),
            $debugLogger,
        );
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    // @readalizer-suppress FunctionVerbNameRule, NoArrayReturnRule
    public function beforeTraverse(array $nodes): array
    {
        return $nodes;
    }

    public function enterNode(Node $node): Node
    {
        $this->scopeStack->push($node);
        $this->violations = $this->violations->merge($this->collectViolations($node));

        return $node;
    }

    public function leaveNode(Node $node): Node
    {
        $this->scopeStack->pop($node);

        return $node;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    // @readalizer-suppress FunctionVerbNameRule, NoArrayReturnRule
    public function afterTraverse(array $nodes): array
    {
        return $nodes;
    }

    public function getViolations(): RuleViolationCollection
    {
        return $this->violations;
    }

    private function collectViolations(Node $node): RuleViolationCollection
    {
        $violations = RuleViolationCollection::create([]);
        $rules = $this->ruleResolver->getRulesForNode($node);

        foreach ($rules as $rule) {
            $this->debugLogger->reportRuleFirstHit($rule::class);
            $violations = $violations->merge(
                $this->filterSuppressed($node, $rule->processNode($node, $this->filePath))
            );
        }

        return $violations;
    }

    /**
     * @param RuleViolationCollection $violations
     * @return RuleViolationCollection
     */
    private function filterSuppressed(Node $node, RuleViolationCollection $violations): RuleViolationCollection
    {
        $methodScope = $this->scopeStack->getCurrentMethod();
        $classScope  = $this->scopeStack->getCurrentClass();
        $filtered    = RuleViolationCollection::create([]);

        foreach ($violations as $v) {
            if (!$this->checker->isSuppressed($node, $v, $methodScope, $classScope)) {
                $filtered = $filtered->merge(RuleViolationCollection::create([$v]));
            }
        }

        return $filtered;
    }
}
