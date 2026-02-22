<?php

/**
 * Factory for creating Analyser instances.
 *
 * This factory encapsulates the logic for constructing Analyser instances
 * along with their dependencies, adhering to the principle of separating
 * object construction from its representation.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class AnalyserFactory
{
    /**
     * @param RuleCollection $rules The rules to apply during analysis.
     * @param string[]|null $ignorePaths File paths, directory prefixes, or glob patterns to exclude.
     */
    public static function create(
        RuleCollection $rules,
        ?array $ignorePaths = null
    ): Analyser {
        $ignorePaths ??= [];
        $pathFilter = PathFilter::create($ignorePaths);
        $phpFileParser = PhpFileParser::create();
        $pathResolver = PathResolver::create($pathFilter);
        $dependencies = AnalyserDependency::create($pathFilter, $phpFileParser, $pathResolver);

        return Analyser::createForFactory($rules, $dependencies, false);
    }

    /**
     * @param RuleCollection $rules The rules to apply during analysis.
     * @param string[]|null $ignorePaths File paths, directory prefixes, or glob patterns to exclude.
     */
    public static function createDebug(
        RuleCollection $rules,
        ?array $ignorePaths = null
    ): Analyser {
        $ignorePaths ??= [];
        $pathFilter = PathFilter::create($ignorePaths);
        $phpFileParser = PhpFileParser::create();
        $pathResolver = PathResolver::create($pathFilter);
        $dependencies = AnalyserDependency::create($pathFilter, $phpFileParser, $pathResolver);

        return Analyser::createForFactory($rules, $dependencies, true);
    }
}
