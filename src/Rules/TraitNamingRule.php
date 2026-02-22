<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Traits should use the "Has" prefix to describe the capability they provide
 * (e.g. HasTimestamps, HasOptions, HasCookieManagement).
 *
 * This makes a class's composition immediately readable:
 *   use HasTimestamps, HasSoftDeletes, HasSlug;
 *
 */
final class TraitNamingRule implements RuleContract
{
    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Trait_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Trait_ $node */
        if ($node->name === null) {
            return RuleViolationCollection::create([]);
        }

        $name = $node->name->toString();

        if (str_starts_with($name, 'Has')) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([
            RuleViolation::createFromDetails(
                message:   sprintf('Trait "%s" should use the "Has" prefix (e.g. "Has%s").', $name, $name),
                filePath:  $filePath,
                line:      $node->getStartLine(),
                ruleClass: self::class,
            ),
        ]);
    }
}
