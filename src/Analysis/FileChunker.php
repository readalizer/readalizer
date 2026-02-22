<?php

/**
 * Splits file lists into worker-sized chunks.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class FileChunker
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function createChunks(PathCollection $files, int $jobs): FileChunkCollection
    {
        $chunkSize = max(1, (int) ceil($files->count() / max(1, $jobs)));

        $chunks = [];
        $buffer = [];

        foreach ($files as $file) {
            $buffer[] = $file;
            if (count($buffer) >= $chunkSize) {
                $chunks[] = PathCollection::create($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            $chunks[] = PathCollection::create($buffer);
        }

        return FileChunkCollection::create($chunks);
    }
}
