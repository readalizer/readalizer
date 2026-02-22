<?php

// @readalizer-suppress NoExecutableCodeInFilesRule, NoMixedPhpHtmlRule

declare(strict_types=1);

use Readalizer\Readalizer\Rulesets\ClassDesignRuleset;
use Readalizer\Readalizer\Rulesets\ExpressionRuleset;
use Readalizer\Readalizer\Rulesets\FileStructureRuleset;
use Readalizer\Readalizer\Rulesets\MethodDesignRuleset;
use Readalizer\Readalizer\Rulesets\NamingRuleset;
use Readalizer\Readalizer\Rulesets\TypeSafetyRuleset;

return [
    'paths' => ['.',],
    'memory_limit' => '2G',
    'cache' => [
        'enabled' => true,
        'path' => '.readalizer-cache.json',
    ],
    'baseline' => '.readalizer-baseline.json',
    'ignore' => [
        'vendor',
        '.php-cs-fixer.dist.php',
        'phpstan-stubs',
        'rector.php',
    ],
    'ruleset' => [
        new FileStructureRuleset(),
        new TypeSafetyRuleset(),
        new ClassDesignRuleset(),
        new MethodDesignRuleset(),
        new NamingRuleset(),
        new ExpressionRuleset(),
    ],
    'rules' => [],
];
