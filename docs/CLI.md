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
- `--format=text|json`
  Selects text or JSON output formatting.
- `--output=<file>`
  Writes the formatted report to a file (timing still prints to stderr).
- `--baseline=<file>`
  Suppresses violations found in the baseline JSON file.
- `--generate-baseline=<file>`
  Writes current violations to a baseline JSON file.
- `--max-violations=<n>`
  Stops collecting after `n` violations and caps the report (`0` means unlimited).
- `--cache` / `--no-cache`
  Enables or disables the on-disk per-file result cache.
- `--progress` / `--no-progress`
  Forces the progress bar on or off.
- `--debug`
  Enables per-rule debug logging. This is currently applied in worker mode.
- `--init`
  Creates `readalizer.php` from `readalizer.php.example` in the current directory.

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
