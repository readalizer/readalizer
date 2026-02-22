<?php

/**
 * Bundles inputs for a single analysis run.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Command;

use Millerphp\Readalizer\Analysis\AnalysisScope;
use Millerphp\Readalizer\Analysis\ParallelRunConfig;
use Millerphp\Readalizer\Analysis\RuleCollection;

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
