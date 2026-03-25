<?php

declare(strict_types=1);

namespace MobilityWork\Domain\Ticket\Exception;

use RuntimeException;
use Throwable;

/**
 * Thrown when the underlying ticket repository fails to create a ticket.
 *
 * Using a domain-specific exception means callers never need to catch
 * SDK-level exceptions (e.g. Zendesk\API\Exceptions\ApiResponseException).
 * The infrastructure adapter is responsible for translating those into this.
 */
final class TicketCreationFailedException extends RuntimeException
{
    public static function withReason(string $reason, ?Throwable $previous = null): self
    {
        return new self(
            \sprintf('Failed to create ticket: %s', $reason),
            0,
            $previous,
        );
    }
}
