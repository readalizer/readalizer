<?php

/**
 * Validates internal worker invocation credentials.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Command;

use Readalizer\Readalizer\Console\Input;

final class WorkerAuthorization
{
    private const ENV_INTERNAL = 'READALIZER_INTERNAL';
    private const ENV_INTERNAL_VALUE = '1';
    private const OPTION_TOKEN = '--worker-token';
    private const OPTION_TOKEN_FILE = '--worker-token-file';

    private function __construct(private readonly Input $input)
    {
    }

    public static function create(Input $input): self
    {
        return new self($input);
    }

    public function isAuthorized(): bool
    {
        if (getenv(self::ENV_INTERNAL) !== self::ENV_INTERNAL_VALUE) {
            return false;
        }

        $token = $this->input->getOption(self::OPTION_TOKEN);
        $tokenFile = $this->input->getOption(self::OPTION_TOKEN_FILE);
        if ($token === null || $tokenFile === null) {
            return false;
        }

        $stored = @file_get_contents($tokenFile);
        if (!is_string($stored)) {
            return false;
        }

        return trim($stored) === $token;
    }
}
