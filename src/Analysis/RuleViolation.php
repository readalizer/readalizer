<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class RuleViolation
{
    private function __construct(
        private readonly string $message,
        private readonly string $filePath,
        private readonly int $line,
        private readonly string $ruleClass,
    ) {}

    public static function createFromDetails(
        string $message,
        string $filePath,
        int $line,
        string $ruleClass,
    ): self {
        return new self(
            message: $message,
            filePath: $filePath,
            line: $line,
            ruleClass: $ruleClass,
        );
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getRuleClass(): string
    {
        return $this->ruleClass;
    }
}
