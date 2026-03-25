<?php

declare(strict_types=1);

namespace MobilityWork\Domain\Ticket\ValueObject;

/**
 * Wraps the Zendesk ticket ID returned after creation.
 *
 * The original methods always returned `true`, discarding the created
 * ticket ID and making it impossible for callers to link back to the
 * resource (e.g. to redirect the user to their ticket URL).
 */
final readonly class TicketId
{
    public function __construct(
        private int $value,
    ) {
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
