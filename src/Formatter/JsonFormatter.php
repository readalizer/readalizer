<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Formatter;

use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use Readalizer\Readalizer\Contracts\FormatterContract;

final class JsonFormatter implements FormatterContract
{
    public function format(RuleViolationCollection $violations): string
    {
        $items = [];
        foreach ($violations as $v) {
            $items[] = [
                'file'    => $v->getFilePath(),
                'line'    => $v->getLine(),
                'message' => $v->getMessage(),
                'rule'    => $v->getRuleClass(),
            ];
        }
        return json_encode(
            [
                'violations' => $items,
                'total'      => $violations->count(),
                'passed'     => $violations->count() === 0,
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ) . "\n";
    }
}
