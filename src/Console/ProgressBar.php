<?php

/**
 * Simple CLI progress bar rendered to STDERR.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Console;

final class ProgressBar
{
    private const BAR_WIDTH = 30;

    private int $current = 0;
    private float $startedAt;
    private bool $enabled = false;

    private function __construct(private int $total)
    {
        $this->startedAt = microtime(true);
    }

    public static function createEnabled(int $total): self
    {
        $self = new self(max(0, $total));
        $self->enabled = true;
        return $self;
    }

    public static function createDisabled(int $total): self
    {
        return new self(max(0, $total));
    }

    public function setTotal(int $total): void
    {
        $this->total = max(0, $total);
    }

    public function update(): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->current++;
        $this->render();
    }

    public function addSteps(int $count): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->current += max(0, $count);
        $this->render();
    }

    public function reportCompletion(): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->current = $this->total;
        $this->render();
        fwrite(STDERR, "\n");
    }

    private function render(): void
    {
        $total = max(1, $this->total);
        $ratio = min(1, $this->current / $total);
        $filled = (int) round(self::BAR_WIDTH * $ratio);
        $bar = str_repeat('#', $filled) . str_repeat('-', self::BAR_WIDTH - $filled);
        $percent = (int) floor($ratio * 100);
        $elapsed = (int) (microtime(true) - $this->startedAt);

        $line = sprintf("\r[%s] %3d%% (%d/%d) %ds", $bar, $percent, $this->current, $this->total, $elapsed);
        fwrite(STDERR, $line);
    }
}
