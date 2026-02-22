<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node\Stmt;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\InlineHTML;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Every PHP file must open with declare(strict_types=1).
 */
final class StrictTypesDeclarationRule implements FileRuleContract
{
    private const MESSAGE = "Missing declare(strict_types=1). All PHP files must declare strict types.";
    private const STRICT_TYPES = 'strict_types';

    /** @param Stmt[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        if ($this->hasStrictTypesDeclaration($ast)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message: self::MESSAGE,
            filePath: $filePath,
            line: 1,
            ruleClass: self::class,
        )]);
    }

    /** @param Stmt[] $ast */
    private function hasStrictTypesDeclaration(array $ast): bool
    {
        foreach ($ast as $stmt) {
            if ($stmt instanceof InlineHTML) {
                continue;
            }

            if (!$stmt instanceof Declare_) {
                return false;
            }

            return $this->isStrictTypesDeclaration($stmt);
        }

        return false;
    }

    private function isStrictTypesDeclaration(Declare_ $stmt): bool
    {
        foreach ($stmt->declares as $declare) {
            if ($declare->key->toString() === self::STRICT_TYPES
                && $declare->value instanceof Int_
                && $declare->value->value === 1
            ) {
                return true;
            }
        }

        return false;
    }
}
