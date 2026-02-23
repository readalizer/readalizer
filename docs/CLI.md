# CLI Usage

## Basic Usage

```bash
php vendor/bin/readalizer
php vendor/bin/readalizer src/ lib/
```

If no paths are provided, `readalizer.php` is used for `paths`.

## Initialize Config

Create a local config file from the example template:

```bash
php vendor/bin/readalizer --init
```

## Options Implemented

These options are currently wired into runtime behavior.

- `--config=<file>`
  Uses a custom configuration file.
- `--jobs=<n>`
  Enables parallel workers when `n > 1`.
- `--worker-timeout=<s>`
  Kills worker processes after N seconds.
- `--memory-limit=<v>`
  Sets the PHP memory limit for analysis.
- `--progress` / `--no-progress`
  Forces the progress bar on or off.
- `--debug`
  Enables per-rule debug logging. This is currently applied in worker mode.
- `--init`
  Creates `readalizer.php` from `readalizer.php.example` in the current directory.

## Options Listed in `--help`

The CLI help lists additional options such as `--format`, `--cache`, `--baseline`, and `--max-violations`. These are parsed in configuration but not yet enforced by the runtime. They are reserved for future work.

If you plan to implement them, start in:

- `src/Command/AnalyseCommand.php`
- `src/Config/ConfigurationLoader.php`
- `src/Analysis/Analyser.php`

## Exit Codes

- `0` if no violations were found.
- `1` if any violations were found.

## Internal Worker Mode

`--_worker` and the `--worker-*` flags are for internal use only.

Workers are launched by `ParallelRunner`. They use a temporary file list and emit a JSON payload that is merged by the parent process. See [PARALLEL.md](PARALLEL.md) for details.

## Local Testing

If you are running the CLI inside this repo, use `php bin/readalizer` instead of `php vendor/bin/readalizer`.

## See Also

- [CONFIGURATION.md](CONFIGURATION.md)
- [PARALLEL.md](PARALLEL.md)
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
