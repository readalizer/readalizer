<?php

/**
 * Container for parallel analysis inputs.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class ParallelRunRequest
{
    private function __construct(
        private readonly RuleCollection $rules,
        private readonly AnalysisScope $targets,
        private readonly ParallelRunConfig $options
    ) {
    }

    public static function create(
        RuleCollection $rules,
        AnalysisScope $targets,
        ParallelRunConfig $options
    ): self {
        return new self($rules, $targets, $options);
    }

    public function getRules(): RuleCollection
    {
        return $this->rules;
    }

    public function getTargets(): AnalysisScope
    {
        return $this->targets;
    }

    public function getOptions(): ParallelRunConfig
    {
        return $this->options;
    }
}
