<?php

/**
 * Bundles inputs for a single analysis run.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

use Readalizer\Readalizer\Analysis\AnalysisScope;
use Readalizer\Readalizer\Analysis\ParallelRunConfig;
use Readalizer\Readalizer\Analysis\RuleCollection;

final class AnalyseCommandContext
{
    private function __construct(
        private readonly RuleCollection $rules,
        private readonly AnalysisScope $targets,
        private readonly ParallelRunConfig $options,
        private readonly ParallelRunEnvironment $environment
    ) {
    }

    public static function create(
        RuleCollection $rules,
        AnalysisScope $targets,
        ParallelRunConfig $options,
        ParallelRunEnvironment $environment
    ): self {
        return new self($rules, $targets, $options, $environment);
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

    public function getEnvironment(): ParallelRunEnvironment
    {
        return $this->environment;
    }
}
