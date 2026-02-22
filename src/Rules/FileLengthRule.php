<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

/**
 * Files should stay reasonably small.
 */
final class FileLengthRule implements FileRuleContract
{
    public function __construct(private readonly int $maxLines = 400) {}

    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $count = $this->countLines($filePath);

        if ($count <= $this->maxLines) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('File is %d lines long (max %d). Consider splitting.', $count, $this->maxLines),
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    private function countLines(string $filePath): int
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        return $lines === false ? 0 : count($lines);
    }
}
