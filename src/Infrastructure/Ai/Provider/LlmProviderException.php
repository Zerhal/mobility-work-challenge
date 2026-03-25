<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai\Provider;

use RuntimeException;
use Throwable;

/**
 * Thrown when an LLM provider call fails for any reason
 * (network error, rate limit, invalid response…).
 *
 * Callers catch this to trigger fallback behaviour — never to crash.
 */
final class LlmProviderException extends RuntimeException
{
    public static function fromThrowable(string $provider, Throwable $previous): self
    {
        return new self(
            \sprintf('[%s] LLM call failed: %s', $provider, $previous->getMessage()),
            0,
            $previous,
        );
    }
}
