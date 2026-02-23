# Configuration

Readalizer uses a PHP config file that returns an array. The default config file is `readalizer.php` in the current working directory.

You can generate it from the template with:

```bash
php vendor/bin/readalizer --init
```

## Example

```php
<?php

use Readalizer\Readalizer\Rules\NoLongMethodsRule;
use Readalizer\Readalizer\Rulesets\DefaultRuleset;
use Readalizer\Readalizer\Rules\LineLengthRule;

return [
    'paths' => ['src/', 'app/'],
    'ignore' => ['vendor/', 'var/'],
    'memory_limit' => '2G',
    'ruleset' => [new DefaultRuleset()],
    'rules' => [
        new NoLongMethodsRule(maxLines: 30),
        new LineLengthRule(maxLength: 120),
    ],
    'cache' => [
        'enabled' => true,
        'path' => '.readalizer-cache.json',
    ],
    'baseline' => '.readalizer-baseline.json',
    'max_violations' => 5000,
];
```

## Keys

- `paths` (`string[]`)
  Paths to scan when no CLI paths are provided.
- `ignore` (`string[]`)
  Path prefixes or glob patterns to skip.
- `rules` (`RuleContract[]|FileRuleContract[]`)
  Rule instances to apply.
- `ruleset` (`RulesetContract[]`)
  Ruleset instances. These are expanded into rules.
- `memory_limit` (`string`)
  Default PHP memory limit for analysis.
- `cache` (`array{enabled?: bool, path?: string}`)
  Cache configuration. Parsed but not yet enforced by runtime.
- `baseline` (`string`)
  Baseline file path. Parsed but not yet enforced by runtime.
- `max_violations` (`int`)
  Max violations to collect. Parsed but not yet enforced by runtime.

## Precedence

- CLI paths override `paths` in config.
- `--memory-limit` overrides `memory_limit`.
- `--config` overrides the default config file location.

## See Also

- [CLI.md](CLI.md)
- [RULES.md](RULES.md)
