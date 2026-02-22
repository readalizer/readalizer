<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;

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
    ) {
        $this->nodeRules = $rules->getNodeRules();
        $this->fileRules = $rules->getFileRules();
    }

    public static function createForFactory(RuleCollection $rules, AnalyserDependency $dependencies, bool $debug): self
    {
        return new self($rules, $dependencies, $debug);
    }

    public static function createDebugForFactory(RuleCollection $rules, AnalyserDependency $dependencies): self
    {
        return self::createForFactory($rules, $dependencies, true);
    }

    /**
     * Analyse a list of files or directories and return all violations found.
     *
     * @param PathCollection $paths
     */
    public function analyse(
        PathCollection $paths,
        ?\Readalizer\Readalizer\Console\ProgressBar $progress = null
    ): AnalysisResult {
        $violations = RuleViolationCollection::create([]);

        if ($progress === null) {
            foreach ($this->dependencies->pathResolver->resolve($paths) as $file) {
                $violations = $violations->merge($this->analyseFile($file)->getRuleViolationCollection());
            }

            return AnalysisResult::create($violations);
        }

        $files = iterator_to_array($this->dependencies->pathResolver->resolve($paths), false);
        $progress->setTotal(count($files));
        foreach ($files as $file) {
            $violations = $violations->merge($this->analyseFile($file)->getRuleViolationCollection());
            $progress->update();
        }
        $progress->reportCompletion();

        return AnalysisResult::create($violations);
    }

    /**
     */
    public function analyseFile(string $filePath): AnalysisResult
    {
        $this->reportDebugMessage(sprintf('file:start %s', $filePath));
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

        return AnalysisResult::create($violations);
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
