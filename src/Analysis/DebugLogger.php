<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class DebugLogger
{
    private const ENABLED_FLAG_ON = 1;
    private const ENABLED_FLAG_OFF = 0;

    /** @var array<string, true> */
    private array $loggedRules = [];

    private function __construct(
        private readonly string $filePath,
        private readonly int $enabledFlag,
    ) {
    }

    public static function createEnabled(string $filePath): self
    {
        return new self($filePath, self::ENABLED_FLAG_ON);
    }

    public static function createDisabled(string $filePath): self
    {
        return new self($filePath, self::ENABLED_FLAG_OFF);
    }

    public function reportRuleFirstHit(string $ruleClass): void
    {
        if ($this->enabledFlag === self::ENABLED_FLAG_OFF) {
            return;
        }

        if (isset($this->loggedRules[$ruleClass])) {
            return;
        }

        $this->loggedRules[$ruleClass] = true;
        $this->report(sprintf(
            'rule:process file=%s rule=%s',
            $this->filePath,
            $ruleClass,
        ));
    }

    public function report(string $message): void
    {
        if ($this->enabledFlag === self::ENABLED_FLAG_OFF) {
            return;
        }

        fwrite(STDERR, sprintf("[debug][pid:%d] %s\n", getmypid(), $message));
    }
}
