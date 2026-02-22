<?php

/**
 * Describes file targets and ignore rules for analysis.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class AnalysisScope
{
    /**
     * @param array<int, string> $ignore
     */
    private function __construct(
        private readonly PathCollection $paths,
        private readonly array $ignore
    ) {
    }

    /**
     * @param array<int, string> $ignore
     */
    public static function create(PathCollection $paths, array $ignore): self
    {
        return new self($paths, $ignore);
    }

    public function getPaths(): PathCollection
    {
        return $this->paths;
    }

    /**
     * @return iterable<int, string>
     */
    public function getIgnore(): iterable
    {
        return $this->ignore;
    }
}
