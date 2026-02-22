# Suppression

Readalizer supports both attribute-based and inline comment suppression.

## Attribute Suppression

Use `#[Suppress]` on classes, methods, or properties.

```php
use Millerphp\Readalizer\Attributes\Suppress;
use Millerphp\Readalizer\Rules\NoLongMethodsRule;

#[Suppress(NoLongMethodsRule::class)]
final class LegacyService
{
    // ...
}
```

Behavior:

- On a class: suppresses rules for all nodes in the class.
- On a method: suppresses rules for all nodes inside the method.
- On a property or parameter: suppresses for that node only.
- With no arguments: suppresses all rules.

## Inline Suppression

Inline suppression uses `// @readalizer-suppress`.

```php
// @readalizer-suppress NoLongMethodsRule
function legacy(): void
{
    // ...
}
```

You can provide either the short rule name or the fully qualified class name.

To suppress all rules at a line, omit the rule list:

```php
// @readalizer-suppress
```

Inline suppression applies to the current line and any following lines until another suppression line overrides it.

