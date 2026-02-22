<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoMixedDocblockRule implements FileRuleContract
{
    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return RuleViolationCollection::create([]);
        }

        foreach ($lines as $i => $line) {
            if ($this->hasMixedTag($line)) {
                return RuleViolationCollection::create([RuleViolation::createFromDetails(
                    message:   'Docblock uses mixed. Use a specific type.',
                    filePath:  $filePath,
                    line:      $i + 1,
                    ruleClass: self::class,
                )]);
            }
        }

        return RuleViolationCollection::create([]);
    }

    private function hasMixedTag(string $line): bool
    {
        return preg_match('/@(?:var|param|return)\s+mixed/i', $line) === 1;
    }
}
