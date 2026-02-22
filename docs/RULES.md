# Rules and Rulesets

Readalizer supports two rule types:

- Node rules implement `RuleContract` and run on specific AST node types.
- File rules implement `FileRuleContract` and see the full file AST.

## Node Rules

```php
<?php

declare(strict_types=1);

namespace App\Readalizer;

use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

final class NoEmptyMethodRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod $node */
        if ($node->stmts !== []) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([
            RuleViolation::createFromDetails(
                message: 'Method must not be empty.',
                filePath: $filePath,
                line: $node->getStartLine(),
                ruleClass: self::class,
            ),
        ]);
    }
}
```

## File Rules

```php
<?php

declare(strict_types=1);

namespace App\Readalizer;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;
use Readalizer\Readalizer\Contracts\FileRuleContract;
use PhpParser\Node;

final class NoExecutableCodeInFilesRule implements FileRuleContract
{
    /** @param Node[] $ast */
    public function processFile(array $ast, string $filePath): RuleViolationCollection
    {
        // inspect top-level AST statements here
        return RuleViolationCollection::create([]);
    }
}
```

## Rulesets

Rulesets are simple bundles of rules.

```php
use Readalizer\Readalizer\Contracts\RulesetContract;

final class MyRuleset implements RulesetContract
{
    public function getRules(): array
    {
        return [new NoEmptyMethodRule()];
    }
}
```

## Built-In Rulesets

The default rulesets are provided under `src/Rulesets/` and cover file structure, naming, method design, class design, type safety, and expressions. For example, `FileStructureRuleset` includes `LineLengthRule` with a default max length of 120 characters.

## Performance Notes

- Keep `processNode` minimal. It runs for every matching AST node.
- Avoid expensive filesystem calls inside rules.
- Prefer early exits and tight loops.
