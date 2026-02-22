<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoMultipleDeclareStrictTypesRule implements FileRuleContract
{
    private const STRICT_TYPES = 'strict_types';

    /** @param Stmt[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $count = $this->countStrictTypes($ast);

        if ($count <= 1) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   'Multiple strict_types declarations detected. Keep a single declare(strict_types=1).',
            filePath:  $filePath,
            line:      1,
            ruleClass: self::class,
        )]);
    }

    /** @param Stmt[] $ast */
    private function countStrictTypes(array $ast): int
    {
        $count = 0;

        foreach ($ast as $stmt) {
            if ($stmt instanceof Declare_ && $this->isStrictTypes($stmt)) {
                $count++;
            }
        }

        return $count;
    }

    private function isStrictTypes(Declare_ $stmt): bool
    {
        foreach ($stmt->declares as $declare) {
            if ($declare->key->toString() === self::STRICT_TYPES) {
                return true;
            }
        }

        return false;
    }
}
