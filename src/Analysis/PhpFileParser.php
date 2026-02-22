<?php

/**
 * Parses PHP source code into an Abstract Syntax Tree (AST).
 *
 * This class encapsulates the logic for reading a PHP file and converting
 * its contents into a structured AST representation using nikic/php-parser.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

/**
 * Parses PHP source code into an Abstract Syntax Tree (AST).
 *
 * This class encapsulates the logic for reading a PHP file and converting
 * its contents into a structured AST representation using nikic/php-parser.
 */
final class PhpFileParser
{

    private function __construct(
        private readonly \PhpParser\Parser $parser
    ) {
    }

    public static function create(): self
    {
        return new self((new ParserFactory())->createForHostVersion());
    }

    /**
     * @return list<Stmt>|null
     */
    // @readalizer-suppress NoArrayReturnRule
    public function parseFile(string $filePath): ?array
    {
        $code = $this->readFile($filePath);
        return $this->parseAst($code);
    }

    private function readFile(string $filePath): string
    {
        $code = file_get_contents($filePath);

        if ($code === false) {
            throw new \RuntimeException("Cannot read file: {$filePath}");
        }

        return $code;
    }

    /**
     * @return list<Stmt>|null
     */
    // @readalizer-suppress NoArrayReturnRule
    private function parseAst(string $code): ?array
    {
        $ast = $this->parser->parse($code);
        if ($ast === null) {
            return null;
        }

        return array_values($ast);
    }
}
