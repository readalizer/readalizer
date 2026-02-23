# Contributing

Thanks for your interest in contributing to Readalizer.

## Development Setup

```bash
composer install
cp readalizer.php.example readalizer.php
php bin/readalizer src
```

## Guidelines

- Use PHP 8.1+ and `declare(strict_types=1);` in all files.
- Keep classes `final` unless abstraction is required.
- Prefer small, focused classes with clear responsibilities.
- Avoid heavy work in constructors.
- Follow existing naming conventions and rule patterns.

## Testing

There is no automated test suite. Validate changes manually:

- `php bin/readalizer` in this repo (local testing)
- `php vendor/bin/readalizer` on a large external codebase

## Submitting Changes

- Keep PRs small and focused.
- Include a short summary, rationale, and sample CLI output for behavior changes.
- Document any new CLI flags or config keys in `docs/` (mirrored at `https://readalizer.github.io/website/`).
