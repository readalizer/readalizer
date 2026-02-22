<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Rules;

use Readalizer\Readalizer\Analysis\RuleViolation;
use Readalizer\Readalizer\Analysis\NodeTypeCollection;
use Readalizer\Readalizer\Contracts\RuleContract;
use Readalizer\Readalizer\Rules\Concerns\HasMagicMethods;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Readalizer\Readalizer\Analysis\RuleViolationCollection;

/**
 * Function and method names should start with an action verb.
 *
 */
final class FunctionVerbNameRule implements RuleContract
{
    use HasMagicMethods;
    /** @var string[] */
    private const VERBS = [
        'get', 'set', 'is', 'has', 'can', 'should', 'add', 'create', 'build',
        'fetch', 'load', 'save', 'update', 'delete', 'remove', 'find', 'make',
        'resolve', 'compute', 'calculate', 'validate', 'parse', 'render',
        'format', 'apply', 'collect', 'prepare', 'handle', 'process', 'ensure',
        'check', 'map', 'filter', 'reduce', 'merge', 'split', 'extract', 'transform',
        'analyse', 'analyze', 'read', 'run', 'append', 'push', 'pop', 'enter',
        'leave', 'match', 'matches', 'detect', 'count', 'group', 'max', 'min',
        'short', 'shorten', 'style', 'build', 'compose', 'assemble', 'list', 'load',
        'declare', 'starts', 'ends', 'returns', 'looks', 'does', 'write', 'report',
    ];

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        /** @var ClassMethod|Function_ $node */
        if ($node instanceof ClassMethod && $this->isMagicMethod($node)) {
            return RuleViolationCollection::create([]);
        }

        if (!$node->name instanceof \PhpParser\Node\Identifier) {
            return RuleViolationCollection::create([]);
        }

        $nameValue = $node->name->toString();
        $name = strtolower($nameValue);

        if ($this->startsWithVerb($name)) {
            return RuleViolationCollection::create([]);
        }

        return RuleViolationCollection::create([RuleViolation::createFromDetails(
            message:   sprintf('Name "%s" should start with a verb (e.g. get, build, calculate).', $nameValue),
            filePath:  $filePath,
            line:      $node->getStartLine(),
            ruleClass: self::class,
        )]);
    }

    private function startsWithVerb(string $name): bool
    {
        foreach (self::VERBS as $verb) {
            if (str_starts_with($name, $verb)) {
                return true;
            }
        }

        return false;
    }
}
