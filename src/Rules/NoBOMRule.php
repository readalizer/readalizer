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
 * UTF-8 BOMs can confuse tooling and diffs.
 */
final class NoBOMRule implements FileRuleContract
{
    private const UTF8_BOM = "\xEF\xBB\xBF";

    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $bytes = $this->readBytes($filePath, 3);

        if ($bytes != self::UTF8_BOM) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'UTF-8 BOM detected. Remove the byte order mark.',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    private function readBytes(string $filePath, int $length): string
    {
        if ($length < 1) {
            return '';
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            return '';
        }

        $bytes = fread($handle, $length);
        fclose($handle);

        return $bytes === false ? '' : $bytes;
    }
}
