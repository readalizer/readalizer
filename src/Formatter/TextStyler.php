<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Formatter;

final class TextStyler
{
    private const MODE_AUTO = 0;
    private const MODE_FORCE = 1;
    private const MODE_DISABLE = 2;

    private const STYLE_RED = 'red';
    private const STYLE_GREEN = 'green';
    private const STYLE_CYAN = 'cyan';
    private const STYLE_BOLD = 'bold';
    private const STYLE_DIM = 'dim';
    private const TERM_DUMB = 'dumb';

    private function __construct(private readonly int $mode)
    {
    }

    // @readalizer-suppress NoStaticMethodsRule
    public static function createAuto(): self
    {
        return new self(self::MODE_AUTO);
    }

    // @readalizer-suppress NoStaticMethodsRule
    public static function createWithAnsi(): self
    {
        return new self(self::MODE_FORCE);
    }

    // @readalizer-suppress NoStaticMethodsRule
    public static function createWithoutAnsi(): self
    {
        return new self(self::MODE_DISABLE);
    }

    public function style(string $text, string $style): string
    {
        if (!$this->shouldUseAnsi()) {
            return $text;
        }

        $code = $this->getStyleCode($style);

        if ($code === null) {
            return $text;
        }

        return "\033[{$code}m{$text}\033[0m";
    }

    private function shouldUseAnsi(): bool
    {
        if ($this->mode === self::MODE_FORCE) {
            return true;
        }

        if ($this->mode === self::MODE_DISABLE) {
            return false;
        }

        return $this->isAnsiEnabled();
    }

    private function getStyleCode(string $style): ?string
    {
        return match ($style) {
            self::STYLE_RED   => '31',
            self::STYLE_GREEN => '32',
            self::STYLE_CYAN  => '36',
            self::STYLE_BOLD  => '1',
            self::STYLE_DIM   => '2',
            default => null,
        };
    }

    private function isAnsiEnabled(): bool
    {
        if (getenv('NO_COLOR')) {
            return false;
        }

        if (getenv('TERM') === self::TERM_DUMB) {
            return false;
        }

        if (function_exists('stream_isatty')) {
            return stream_isatty(STDOUT);
        }

        return function_exists('posix_isatty') && posix_isatty(STDOUT);
    }
}
