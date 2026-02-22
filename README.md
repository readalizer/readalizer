# Readalizer

A PHP static analysis tool for enforcing readability standards your way.

Readalizer lets you define what readable code looks like and applies those rules consistently across a codebase.

## Installation

```bash
composer require millerphp/readalizer
```

## Quick Start

```bash
cp readalizer.php.example readalizer.php
php bin/readalizer
```

You can also generate the config with:

```bash
php bin/readalizer --init
```

## CLI Examples

```bash
php bin/readalizer src/ lib/
php bin/readalizer --jobs=4 --memory-limit=2G
php bin/readalizer --config=path/to/readalizer.php
```

## Developer Docs

See `docs/README.md` for full developer documentation, including architecture, rules, suppression, configuration, CLI details, and parallel execution.

## Release

Current release: 1.0.0 (2026-02-22)
