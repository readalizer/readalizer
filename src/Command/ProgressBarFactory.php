<?php

/**
 * Builds progress bar instances based on CLI flags.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Command;

use Millerphp\Readalizer\Analysis\PathCollection;
use Millerphp\Readalizer\Console\Input;
use Millerphp\Readalizer\Console\ProgressBar;

final class ProgressBarFactory
{
    private const OPTION_NO_PROGRESS = '--no-progress';
    private const OPTION_PROGRESS = '--progress';

    private function __construct(private readonly Input $input)
    {
    }

    public static function create(Input $input): self
    {
        return new self($input);
    }

    public function build(PathCollection $paths): ?ProgressBar
    {
        if (!$this->shouldShowProgress()) {
            return null;
        }

        return ProgressBar::createEnabled($paths->count());
    }

    private function shouldShowProgress(): bool
    {
        if ($this->input->hasOption(self::OPTION_NO_PROGRESS)) {
            return false;
        }

        if ($this->input->hasOption(self::OPTION_PROGRESS)) {
            return true;
        }

        if (function_exists('stream_isatty')) {
            return stream_isatty(STDERR);
        }

        if (function_exists('posix_isatty')) {
            return posix_isatty(STDERR);
        }

        return false;
    }
}
