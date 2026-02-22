<?php

// @readalizer-suppress NoExecutableCodeInFilesRule, NoMixedPhpHtmlRule

declare(strict_types=1);

use Millerphp\Readalizer\Rulesets\ClassDesignRuleset;
use Millerphp\Readalizer\Rulesets\ExpressionRuleset;
use Millerphp\Readalizer\Rulesets\FileStructureRuleset;
use Millerphp\Readalizer\Rulesets\MethodDesignRuleset;
use Millerphp\Readalizer\Rulesets\NamingRuleset;
use Millerphp\Readalizer\Rulesets\TypeSafetyRuleset;

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
