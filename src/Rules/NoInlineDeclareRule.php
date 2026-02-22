<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Nop;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoInlineDeclareRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $seenNonDeclare = false;

        foreach ($ast as $stmt) {
            if ($stmt instanceof Nop) {
                continue;
            }

            if ($stmt instanceof Declare_) {
                if ($seenNonDeclare) {
                    return RuleViolationCollection::create([RuleViolation::createFromDetails(
                        message:   'Declare statements must appear before any other code.',
                        filePath:  $filePath,
                        line:      $stmt->getStartLine(),
                        ruleClass: self::class,
                    )]);
                }
                continue;
            }

            $seenNonDeclare = true;
        }

        return RuleViolationCollection::create([]);
    }
}
