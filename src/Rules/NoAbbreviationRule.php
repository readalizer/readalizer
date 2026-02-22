<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules;

use Millerphp\Readalizer\Analysis\RuleViolation;
use Millerphp\Readalizer\Analysis\NodeTypeCollection;
use Millerphp\Readalizer\Contracts\RuleContract;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Millerphp\Readalizer\Analysis\RuleViolationCollection;

final class NoAbbreviationRule implements RuleContract
{
    /** @var string[] */
    private const ABBREVIATIONS = ['cfg', 'ctx', 'mgr', 'svc', 'util', 'tmp', 'obj', 'info'];
    private const EMPTY_STRING = '';
    private const SEGMENT_SPLIT_PATTERN = '/[^a-zA-Z0-9]+/';
    private const CAMEL_SPLIT_PATTERN = '/(?<=[a-z0-9])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/';

    public function getNodeTypes(): NodeTypeCollection
    {
        return NodeTypeCollection::create([Class_::class, Property::class, ClassMethod::class, Function_::class]);
    }

    public function processNode(Node $node, string $filePath): RuleViolationCollection
    {
        $names = $this->collectNames($node);

        foreach ($names as $name => $line) {
            if ($this->hasAbbreviation($name)) {
                return RuleViolationCollection::create([RuleViolation::createFromDetails(
                    message:   sprintf('Name "%s" contains discouraged abbreviation.', $name),
                    filePath:  $filePath,
                    line:      $line,
                    ruleClass: self::class,
                )]);
            }
        }

        return RuleViolationCollection::create([]);
    }

    /** @return array<string,int> */
    // @readalizer-suppress NoArrayReturnRule
    private function collectNames(Node $node): array
    {
        if ($node instanceof Class_) {
            return $node->name ? [$node->name->toString() => $node->getStartLine()] : [];
        }

        if ($node instanceof Property) {
            $names = [];
            foreach ($node->props as $prop) {
                $names[$prop->name->toString()] = $prop->getStartLine();
            }
            return $names;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $names = [$node->name->toString() => $node->getStartLine()];
            foreach ($node->params as $param) {
                if ($param->var instanceof Variable && is_string($param->var->name)) {
                    $names[$param->var->name] = $param->getStartLine();
                }
            }
            return $names;
        }

        return [];
    }

    private function hasAbbreviation(string $name): bool
    {
        $segments = $this->splitNameSegments($name);

        foreach (self::ABBREVIATIONS as $abbr) {
            if (in_array($abbr, $segments, true)) {
                return true;
            }
        }

        return $this->hasWordBoundaryAbbreviation($name);
    }

    /** @return array<int, string> */
    private function splitNameSegments(string $name): array
    {
        $chunks = preg_split(self::SEGMENT_SPLIT_PATTERN, $name, -1, PREG_SPLIT_NO_EMPTY);

        if ($chunks === false || $chunks === []) {
            return [];
        }

        $segments = [];

        foreach ($chunks as $chunk) {
            $parts = preg_split(self::CAMEL_SPLIT_PATTERN, $chunk);

            if ($parts === false) {
                $segments[] = strtolower($chunk);
                continue;
            }

            foreach ($parts as $part) {
                if ($part === self::EMPTY_STRING) {
                    continue;
                }
                $segments[] = strtolower($part);
            }
        }

        return $segments;
    }

    private function hasWordBoundaryAbbreviation(string $name): bool
    {
        $pattern = $this->getAbbreviationBoundaryPattern();
        return preg_match($pattern, $name) === 1;
    }

    private function getAbbreviationBoundaryPattern(): string
    {
        $joined = implode('|', array_map('preg_quote', self::ABBREVIATIONS));
        return '/(?<![a-zA-Z0-9])(?:' . $joined . ')(?![a-zA-Z0-9])/i';
    }
}
