<?php

/**
 * Resolves files for analysis based on ignore rules.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class FileCollector
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<int, string> $ignore
     */
    public function collectFiles(PathCollection $paths, array $ignore): PathCollection
    {
        $pathFilter = PathFilter::create($ignore);
        $resolver = PathResolver::create($pathFilter);

        $files = iterator_to_array($resolver->resolve($paths), false);

        return PathCollection::create($files);
    }
}
