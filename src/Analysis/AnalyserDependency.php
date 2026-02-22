<?php

/**
 * Encapsulates the dependencies required by the Analyser class.
 *
 * This value object helps to reduce the parameter list of the Analyser's
 * constructor and named constructors, improving readability and maintainability.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class AnalyserDependency
{
    private function __construct(
        public readonly PathFilter $pathFilter,
        public readonly PhpFileParser $phpFileParser,
        public readonly PathResolver $pathResolver,
    ) {}

    public static function create(
        PathFilter $pathFilter,
        PhpFileParser $phpFileParser,
        PathResolver $pathResolver
    ): self {
        return new self($pathFilter, $phpFileParser, $pathResolver);
    }
}
