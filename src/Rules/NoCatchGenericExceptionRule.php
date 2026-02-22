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
use PhpParser\Node\Stmt\Catch_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

final class NoCatchGenericExceptionRule implements RuleContract
{
    private const EXCEPTION_CLASS = 'Exception';
    private const THROWABLE_CLASS = 'Throwable';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Catch_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var Catch_ $catch */
        $catch = $node;
        foreach ($catch->types as $type) {
            $name = $type->getLast();
            if ($name === self::EXCEPTION_CLASS || $name === self::THROWABLE_CLASS) {
                return RuleViolationCollection::create([RuleViolation::createFromDetails(
                    message:   'Catching generic Exception/Throwable is discouraged.',
                    filePath:  $filePath,
                    line:      $catch->getStartLine(),
                    ruleClass: self::class,
                )]);
            }
        }

        return RuleViolationCollection::create([]);
    }
}
