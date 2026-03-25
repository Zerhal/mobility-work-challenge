<?php

declare(strict_types=1);

namespace MobilityWork\Domain\Ticket\ValueObject;

use InvalidArgumentException;

/**
 * Encapsulates the business rule: a ticket subject is derived from a message
 * and must not exceed 50 characters (truncated with an ellipsis if necessary).
 *
 * Previously this logic was duplicated inline four times:
 *   strlen($message) > 50 ? substr($message, 0, 50) . '...' : $message
 *
 * Centralising it here means the rule lives in one place, is named, and is
 * independently testable.
 */
final class TicketSubject
{
    private const MAX_LENGTH = 50;
    private const ELLIPSIS = '...';

    private readonly string $value;

    public function __construct(string $rawMessage)
    {
        if (trim($rawMessage) === '') {
            throw new InvalidArgumentException('Ticket subject cannot be derived from an empty message.');
        }

        $this->value = mb_strlen($rawMessage) > self::MAX_LENGTH
            ? mb_substr($rawMessage, 0, self::MAX_LENGTH) . self::ELLIPSIS
            : $rawMessage;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
