<?php

/**
 * Stores worker authorization values for spawning.
 */

declare(strict_types=1);

namespace Millerphp\Readalizer\Analysis;

final class WorkerCommandSecurity
{
    private function __construct(
        private readonly string $token,
        private readonly string $tokenFile
    ) {
    }

    public static function create(string $token, string $tokenFile): self
    {
        return new self($token, $tokenFile);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getTokenFile(): string
    {
        return $this->tokenFile;
    }
}
