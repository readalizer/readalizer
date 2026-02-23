<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use Readalizer\Readalizer\Attributes\Suppress;

#[Suppress(
    \Readalizer\Readalizer\Rules\MaxClassLengthRule::class,
    \Readalizer\Readalizer\Rules\NoGodClassRule::class,
)]
final class Analyser
{
    private readonly NodeRuleCollection $nodeRules;
    private readonly FileRuleCollection $fileRules;

    /**
     * @param RuleCollection $rules
     */
    private function __construct(
        RuleCollection $rules,
        private readonly AnalyserDependency $dependencies,
        private readonly bool $debug, // @readalizer-suppress NoBooleanParameterRule
        private readonly ?AnalysisResultCache $cache,
    ) {
        $this->nodeRules = $rules->getNodeRules();
        $this->fileRules = $rules->getFileRules();
    }

    public static function createForFactory(
        RuleCollection $rules,
        AnalyserDependency $dependencies,
        bool $debug,
        ?AnalysisResultCache $cache = null
    ): self
    {
        return new self($rules, $dependencies, $debug, $cache);
    }

    public static function createDebugForFactory(
        RuleCollection $rules,
        AnalyserDependency $dependencies,
        ?AnalysisResultCache $cache = null
    ): self
    {
        return self::createForFactory($rules, $dependencies, true, $cache);
    }

    /**
     * Analyse a list of files or directories and return all violations found.
     *
     * @param PathCollection $paths
     * @param ?\Closure(RuleViolationCollection): RuleViolationCollection $violationFilter
     */
    #[Suppress(
        \Readalizer\Readalizer\Rules\NoLongMethodsRule::class,
        \Readalizer\Readalizer\Rules\MaxNestingDepthRule::class,
    )]
    public function analyse(
        PathCollection $paths,
        ?\Readalizer\Readalizer\Console\ProgressBar $progress = null,
        int $maxViolations = 0,
        ?\Closure $violationFilter = null
    ): AnalysisResult {
        try {
            $violations = RuleViolationCollection::create([]);

            if ($progress === null) {
                foreach ($this->dependencies->pathResolver->resolve($paths) as $file) {
                    $fileViolations = $this->analyseFile($file)->getRuleViolationCollection();
                    if ($violationFilter !== null) {
                        $fileViolations = $violationFilter($fileViolations);
                    }
                    /** @var RuleViolationCollection $fileViolations */
                    $violations = $this->mergeWithLimit(
                        $violations,
                        $fileViolations,
                        $maxViolations
                    );
                    if ($this->hasReachedMaxViolations($violations, $maxViolations)) {
                        break;
                    }
                }

                return AnalysisResult::create($violations);
            }

            $files = iterator_to_array($this->dependencies->pathResolver->resolve($paths), false);
            $progress->setTotal(count($files));
            foreach ($files as $file) {
                $fileViolations = $this->analyseFile($file)->getRuleViolationCollection();
                if ($violationFilter !== null) {
                    $fileViolations = $violationFilter($fileViolations);
                }
                /** @var RuleViolationCollection $fileViolations */
                $violations = $this->mergeWithLimit(
                    $violations,
                    $fileViolations,
                    $maxViolations
                );
                $progress->update();
                if ($this->hasReachedMaxViolations($violations, $maxViolations)) {
                    break;
                }
            }
            $progress->reportCompletion();

            return AnalysisResult::create($violations);
        } finally {
            $this->saveCache();
        }
    }

    /**
     */
    #[Suppress(\Readalizer\Readalizer\Rules\NoLongMethodsRule::class)]
    public function analyseFile(string $filePath): AnalysisResult
    {
        $this->reportDebugMessage(sprintf('file:start %s', $filePath));
        $cachedResult = $this->cache?->get($filePath);
        if ($cachedResult !== null) {
            $this->reportDebugMessage(sprintf('file:cache-hit %s violations=%d', $filePath, $cachedResult->count()));
            return AnalysisResult::create($cachedResult);
        }

        $ast = null;

        try {
            $ast = $this->dependencies->phpFileParser->parseFile($filePath);
        } catch (\PhpParser\Error $e) {
            $this->reportDebugMessage(sprintf('file:parse-error %s error=%s', $filePath, $e->getMessage()));
        }

        $code = file_get_contents($filePath);
        if ($code === false) {
            $code = '';
        }

        if ($ast === null) {
            $this->reportDebugMessage(sprintf('file:parse-null %s', $filePath));
            $violations = RuleViolationCollection::create([]);
        } else {
            /** @var Node[] $ast */
            $violations = $this->processFileAst($ast, $filePath, $code)->getRuleViolationCollection();
        }

        $this->reportDebugMessage(sprintf('file:done %s violations=%d', $filePath, $violations->count()));
        $this->cache?->saveFileResult($filePath, $violations);

        return AnalysisResult::create($violations);
    }

    public function saveCache(): void
    {
        $this->cache?->saveChanges();
    }

    private function mergeWithLimit(
        RuleViolationCollection $current,
        RuleViolationCollection $incoming,
        int $maxViolations
    ): RuleViolationCollection {
        if ($maxViolations <= 0) {
            return $current->merge($incoming);
        }

        $remaining = $maxViolations - $current->count();
        if ($remaining <= 0) {
            return $current;
        }

        return $current->merge($incoming->getLimitedTo($remaining));
    }

    private function hasReachedMaxViolations(RuleViolationCollection $violations, int $maxViolations): bool
    {
        return $maxViolations > 0 && $violations->count() >= $maxViolations;
    }

    /** @param Node[] $ast */
    private function processFileAst(array $ast, string $filePath, string $code): AnalysisResult
    {
        $checker    = SuppressionChecker::create($code);
        $violations = $this->runNodeRules($ast, $filePath, $code)->getRuleViolationCollection();
        $violations = $violations->merge(
            $this->appendFileRuleViolations($ast, $filePath, $checker)->getRuleViolationCollection()
        );

        return AnalysisResult::create($violations);
    }

    /** @param Node[] $ast */
    private function runNodeRules(array $ast, string $filePath, string $code): AnalysisResult
    {
        $checker   = SuppressionChecker::create($code);
        $visitor   = NodeVisitor::create($this->nodeRules, $filePath, $checker, $this->debug);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return AnalysisResult::create($visitor->getViolations());
    }

    /** @param Node[] $ast */
    private function appendFileRuleViolations(array $ast, string $filePath, SuppressionChecker $checker): AnalysisResult
    {
        $violations = RuleViolationCollection::create([]);

        foreach ($this->fileRules as $rule) {
            $this->reportDebugMessage(sprintf('file-rule:start file=%s rule=%s', $filePath, $rule::class));
            foreach ($rule->processFile($ast, $filePath) as $fileViolation) {
                if (!$checker->isRuleViolationSuppressed($fileViolation)) {
                    $violations = $violations->merge(RuleViolationCollection::create([$fileViolation]));
                }
            }
            $this->reportDebugMessage(sprintf('file-rule:done file=%s rule=%s', $filePath, $rule::class));
        }

        return AnalysisResult::create($violations);
    }

    private function reportDebugMessage(string $message): void
    {
        if (!$this->debug) {
            return;
        }

        fwrite(STDERR, sprintf("[debug][pid:%d] %s\n", getmypid(), $message));
    }
}
