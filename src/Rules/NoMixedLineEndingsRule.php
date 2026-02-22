<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoMixedLineEndingsRule implements FileRuleContract
{
    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $code = file_get_contents($filePath);

        if ($code === false) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->hasMixedEndings($code)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Mixed line endings detected. Use a single line ending style.',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    private function hasMixedEndings(string $code): bool
    {
        $hasCrlf = str_contains($code, "
");
        $hasLf = str_contains(str_replace("
", '', $code), "
");

        return $hasCrlf && $hasLf;
    }
}
