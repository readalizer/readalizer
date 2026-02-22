<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Long lines hurt readability and diffing.
 */
final class LineLengthRule implements FileRuleContract
{
    public function __construct(private readonly int $maxLength = 120) {}

    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $violations = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return RuleViolationCollection::create([]);
        }

        foreach ($lines as $i => $line) {
            if (strlen($line) <= $this->maxLength) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   sprintf('Line exceeds %d characters.', $this->maxLength),
                filePath:  $filePath,
                line:      $i + 1,
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }
}
