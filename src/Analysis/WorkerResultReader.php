<?php

/**
 * Reads worker output files into violation collections.
 */

declare(strict_types=1);

namespace Readalizer\Readalizer\Analysis;

final class WorkerResultReader
{
    private const EMPTY_STRING = '';

    private function __construct(private readonly ViolationPayloadCodec $payloadCodec)
    {
    }

    public static function create(ViolationPayloadCodec $payloadCodec): self
    {
        return new self($payloadCodec);
    }

    public function collectViolations(WorkerProcess $process): RuleViolationCollection
    {
        $raw = @file_get_contents($process->getPaths()->getOutputPath());
        if (!is_string($raw) || $raw === self::EMPTY_STRING) {
            return RuleViolationCollection::create([]);
        }

        return $this->payloadCodec->parsePayload($raw);
    }
}
