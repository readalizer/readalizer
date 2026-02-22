<?php

/**
 * A value object representing a collection of filtered rules.
 *
 * This class encapsulates node-specific and file-specific rules,
 * providing a structured and type-safe way to handle them.
*/

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class FilteredRuleset
{
    private function __construct(
        private readonly NodeRuleCollection $nodeRules,
        private readonly FileRuleCollection $fileRules,
    ) {}

    /**
     * Creates a new FilteredRuleset instance.
     */
    public static function create(NodeRuleCollection $nodeRules, FileRuleCollection $fileRules): self
    {
        return new self($nodeRules, $fileRules);
    }

    public function getNodeRules(): NodeRuleCollection
    {
        return $this->nodeRules;
    }

    public function getFileRules(): FileRuleCollection
    {
        return $this->fileRules;
    }
}
