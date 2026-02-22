<?php

/**
 * The main application entry point for the Readalizer CLI tool.
 *
 * This class orchestrates the command execution based on user input,
 * handling global options like '--help' and delegating to specific commands.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer;

use Millerphp\Readalizer\Console\Input;
use Millerphp\Readalizer\Console\Output;
use Millerphp\Readalizer\Command\AnalyseCommand;
use Millerphp\Readalizer\Command\InitCommand;
use Millerphp\Readalizer\Command\WorkerCommand;

final class Application
{
    private function __construct(
        private readonly Input $input,
        private readonly Output $output
    ) {
    }

    /**
     * @param array<int, string> $argv
     */
    public static function create(array $argv, Output $output): self
    {
        return new self(Input::createFromArgv($argv), $output);
    }

    public function run(): int
    {
        if ($this->input->hasOption('--_worker')) {
            $command = WorkerCommand::create($this->input, $this->output);
            return $command->run();
        }

        if ($this->input->hasOption('--help') || $this->input->hasOption('-h')) {
            $this->renderHelp();
            return 0;
        }

        if ($this->input->hasOption('--init')) {
            $command = InitCommand::create($this->output);
            return $command->run();
        }

        $command = AnalyseCommand::create($this->input, $this->output);

        return $command->run();
    }

    private function renderHelp(): void
    {
        $this->output->writeln(
            <<<HELP
        Usage: readalizer [options] [<path> ...]

        Options:
          --format=text|json   Output format (default: text)
          --jobs=<n>           Run analysis in parallel with N worker processes (default: 1)
          --progress           Force progress bar on
          --no-progress        Disable progress bar
          --memory-limit=<v>   Override PHP memory limit (default: 2G)
          --worker-timeout=<s> Kill worker processes that exceed N seconds (default: 120)
          --max-violations=<n> Stop after collecting N violations (0 = unlimited, default: 5000)
          --debug              Print file/rule processing logs to STDERR
          --baseline=<file>    Suppress violations listed in a baseline file
          --generate-baseline=<file>  Write current violations to a baseline file
          --cache              Enable caching
          --no-cache           Disable caching
          --ansi               Force ANSI colors on
          --no-ansi            Disable ANSI colors
          --config=<file>      Config file path (default: readalizer.php in cwd)
          --init               Create readalizer.php from readalizer.php.example
          --help               Show this help

        If no paths are given on the CLI, uses the 'paths' key from the config file.

        HELP
        );
    }
}
