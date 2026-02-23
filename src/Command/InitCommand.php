<?php

/**
 * Creates a local readalizer.php config from the example template.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

use Readalizer\Readalizer\Console\Output;

final class InitCommand
{
    private const TARGET = 'readalizer.php';
    private const TEMPLATE = 'readalizer.php.example';
    private const ERROR_EXISTS = 'readalizer.php already exists.';
    private const ERROR_TEMPLATE_MISSING = 'readalizer.php.example not found.';
    private const ERROR_WRITE_FAILED = 'Failed to write readalizer.php.';
    private const SUCCESS = 'Created readalizer.php from readalizer.php.example.';

    private function __construct(private readonly Output $output)
    {
    }

    public static function create(Output $output): self
    {
        return new self($output);
    }

    public function run(): int
    {
        if (file_exists(self::TARGET)) {
            $this->output->writeError(self::ERROR_EXISTS);
            return 1;
        }

        $templatePath = $this->resolveTemplatePath();
        if ($templatePath === null) {
            $this->output->writeError(self::ERROR_TEMPLATE_MISSING);
            return 1;
        }

        $contents = file_get_contents($templatePath);
        if (!is_string($contents)) {
            $this->output->writeError(self::ERROR_TEMPLATE_MISSING);
            return 1;
        }

        $written = file_put_contents(self::TARGET, $contents);
        if ($written === false) {
            $this->output->writeError(self::ERROR_WRITE_FAILED);
            return 1;
        }

        $this->output->writeln(self::SUCCESS);
        return 0;
    }

    private function resolveTemplatePath(): ?string
    {
        if (file_exists(self::TEMPLATE)) {
            return self::TEMPLATE;
        }

        $packageRoot = dirname(__DIR__, 3);
        $path = $packageRoot . DIRECTORY_SEPARATOR . self::TEMPLATE;
        if (file_exists($path)) {
            return $path;
        }

        return null;
    }
}
