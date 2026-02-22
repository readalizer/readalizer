<?php

/**
 * Resolves and collects PHP files from a given set of paths, respecting ignore patterns.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Resolves and collects PHP files from a given set of paths, respecting ignore patterns.
 *
 * This class encapsulates the logic for traversing directories, filtering files
 * by extension, and applying ignore rules to produce a definitive list of
 * PHP files that should be analyzed.
 */
final class PathResolver
{
    private const PHP_EXTENSION = 'php';

    private function __construct(private readonly PathFilter $pathFilter) {}

    public static function create(PathFilter $pathFilter): self
    {
        return new self($pathFilter);
    }

    /**
     * @return \Generator<string>
     */
    public function resolve(PathCollection $paths): \Generator
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                if (!$this->pathFilter->isIgnored($path)) {
                    yield $path;
                }
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                /** @var SplFileInfo $file */
                if (
                    $file->getExtension() === self::PHP_EXTENSION
                      && !$this->pathFilter->isIgnored($file->getPathname())
                ) {
                    yield $file->getPathname();
                }
            }
        }
    }
}
