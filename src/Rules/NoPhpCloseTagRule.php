<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoPhpCloseTagRule implements FileRuleContract
{
    private const CLOSE_TAG = '?' . '>';

    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $code = file_get_contents($filePath);

        if ($code === false) {
            return RuleViolationCollection::create([]);
        }

        if (!$this->endsWithCloseTag($code)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Closing PHP tag detected. Omit ' . self::CLOSE_TAG . ' in PHP-only files.',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    private function endsWithCloseTag(string $code): bool
    {
        return str_contains($code, self::CLOSE_TAG)
            && rtrim($code) !== rtrim(str_replace(self::CLOSE_TAG, '', $code));
    }
}
