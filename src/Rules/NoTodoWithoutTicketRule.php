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
 * TODOs should always include a ticket reference.
 */
final class NoTodoWithoutTicketRule implements FileRuleContract
{
    /** @param \PhpParser\Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        $violations = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return RuleViolationCollection::create([]);
        }

        foreach ($lines as $i => $line) {
            if (!$this->hasTodo($line) || $this->hasTicket($line)) {
                continue;
            }

            $violations[] = RuleViolation::createFromDetails(
                message:   'TODO comment missing ticket reference (e.g. ABC-123 or #123).',
                filePath:  $filePath,
                line:      $i + 1,
                ruleClass: self::class,
            );
        }

        return RuleViolationCollection::create($violations);
    }

    private function hasTodo(string $line): bool
    {
        return preg_match('/^\s*(\/\/|#|\/\*|\*)\s*TODO\b/i', trim($line)) === 1;
    }

    private function hasTicket(string $line): bool
    {
        return preg_match('/(#[0-9]+|[A-Z]{2,10}-[0-9]+)/', $line) === 1;
    }
}
