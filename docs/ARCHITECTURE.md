# Architecture

This document explains the major components and data flow in Readalizer.

## High-Level Flow

1. `Application` reads CLI args via `Console\Input`.
2. `AnalyseCommand` builds an `AnalyseCommandContext` with config, paths, rules, options, and environment.
3. If `--jobs` is 1, `Analyser` runs sequentially.
4. If `--jobs` is >1, `ParallelRunner` spawns workers and aggregates results.
5. `TextFormatter` writes a report and exit code reflects violations.

## Core Components

- `Application`
  Entry point. Dispatches to `AnalyseCommand` or internal worker mode.

- `AnalyseCommandContextFactory`
  Resolves config, paths, options, and environment in one place.

- `Analyser`
  Parses files, walks AST nodes, and applies rules.

- `RuleCollection`
  Holds node-based rules and file-based rules.

- `ParallelRunner`
  Coordinates parallel analysis when `--jobs` is greater than 1.

- `WorkerCommand`
  Internal entrypoint for a worker process. It reads a file list and writes a JSON payload of violations.

- `Formatter` (`TextFormatter`, `JsonFormatter`)
  Render output. `TextFormatter` is used by the CLI today.

## AST Processing

- Files are parsed by `PhpFileParser` (PhpParser) into AST nodes.
- `NodeVisitor` applies node rules and gathers violations.
- `FileRuleContract` rules can inspect the full AST for a file.

## Suppression

- `#[Suppress]` attribute suppresses rules at class, method, or property scope.
- Inline comment suppression uses `// @readalizer-suppress` with rule names or class names.

See `docs/SUPPRESSION.md` for full details.

## Configuration Boundaries

`ConfigurationLoader` accepts the config array and creates a typed `Configuration` object. Not all config keys are currently enforced by the runtime. See `docs/CONFIGURATION.md`.

