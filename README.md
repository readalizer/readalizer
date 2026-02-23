# Readalizer

A PHP static analysis tool for enforcing readability standards your way.

Readalizer lets you define what readable code looks like and applies those rules consistently across a codebase.

## Installation

```bash
composer require readalizer/readalizer
```

## Quick Start

```bash
cp readalizer.php.example readalizer.php
php vendor/bin/readalizer
```

You can also generate the config with:

```bash
php vendor/bin/readalizer --init
```

## CLI Examples

```bash
php vendor/bin/readalizer src/ lib/
php vendor/bin/readalizer --jobs=4 --memory-limit=2G
php vendor/bin/readalizer --config=path/to/readalizer.php
```

## Developer Docs

Documentation lives in this repo under `docs/` and is mirrored at `https://readalizer.github.io/website/`.

See `docs/README.md` for full developer documentation, including architecture, rules, suppression, configuration, CLI details, and parallel execution.

## Release

Current release: 1.0.0 (2026-02-22)
