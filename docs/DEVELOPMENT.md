# Development Guide

## Requirements

- PHP 8.1+
- Composer

## Local Setup

```bash
composer install
cp readalizer.php.example readalizer.php
php bin/readalizer src
```

## Running Against Another Codebase

You can point Readalizer at any directory by passing it on the CLI.

```bash
php vendor/bin/readalizer /path/to/project
```

If you are working on Readalizer itself and want to test in another repo via Composer, use a local path repository in that project and require `readalizer/readalizer` from the local path.

## Repository Conventions

- `src/` contains core library code grouped by domain.
- `bin/readalizer` is the CLI entrypoint for local repo testing.
- `readalizer.php.example` is the configuration template.
- `vendor/` is Composer-managed and should be ignored by analysis.

## Manual Validation

There is no automated test suite. Validate changes manually.

- Run `php bin/readalizer` on this repo.
- Run `php vendor/bin/readalizer` on a large target codebase to confirm scale, memory usage, and progress behavior.

## Style Requirements

- `declare(strict_types=1);` in all PHP files.
- Classes are `final` by default.
- 4-space indentation and K&R braces.
- Interfaces use `Contract` suffix. Traits use `Has` prefix.

## See Also

- [CLI.md](CLI.md)
- [CONFIGURATION.md](CONFIGURATION.md)
