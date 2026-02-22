<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class PathFilter
{
    /** @param string[] $ignorePaths */
    private function __construct(private readonly array $ignorePaths)
    {
    }

    /** @param string[] $ignorePaths */
    public static function create(array $ignorePaths): self
    {
        return new self($ignorePaths);
    }

    public function isIgnored(string $filePath): bool
    {
        $realPath = realpath($filePath);
        if ($realPath === false) {
            $realPath = $filePath;
        }

        foreach ($this->ignorePaths as $pattern) {
            if ($this->matchesGlob($pattern, $filePath, $realPath)) {
                return true;
            }

            if ($this->matchesDirectoryPrefix($pattern, $filePath, $realPath)) {
                return true;
            }
        }

        return false;
    }

    private function matchesGlob(string $pattern, string $filePath, string $realPath): bool
    {
        return fnmatch($pattern, $filePath) || fnmatch($pattern, $realPath);
    }

    private function matchesDirectoryPrefix(string $pattern, string $filePath, string $realPath): bool
    {
        $realPattern = realpath($pattern);

        if ($realPattern !== false) {
            return $realPath === $realPattern
                || str_starts_with($realPath, $realPattern . DIRECTORY_SEPARATOR);
        }

        return $this->matchesPathPrefix($pattern, $filePath, $realPath);
    }

    private function matchesPathPrefix(string $pattern, string $filePath, string $realPath): bool
    {
        return str_starts_with($filePath, $pattern) || str_starts_with($realPath, $pattern);
    }
}
