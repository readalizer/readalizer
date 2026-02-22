<?php

/**
 * @internal
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Rules\Concerns;

use PhpParser\Node\Stmt\ClassMethod;

trait HasMagicMethods
{
    private const MAGIC_METHODS = [
        '__construct', '__destruct', '__call', '__callstatic', '__get', '__set',
        '__isset', '__unset', '__sleep', '__wakeup', '__serialize', '__unserialize',
        '__tostring', '__invoke', '__set_state', '__clone', '__debuginfo',
    ];

    private function isMagicMethodName(string $name): bool
    {
        return in_array(strtolower($name), self::MAGIC_METHODS, true);
    }

    private function isMagicMethod(ClassMethod $node): bool
    {
        return $this->isMagicMethodName($node->name->toString());
    }
}
