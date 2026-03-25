<?php

declare(strict_types=1);

namespace MobilityWork\Application\Command;

/**
 * CQRS Command — expresses the intent to open a customer support ticket.
 *
 * Commands are immutable data bags. They carry no behaviour.
 * The corresponding handler owns the orchestration logic.
 *
 * Note: $gender and $domainConfig from the original signature are preserved
 * for backward compatibility but marked explicitly. If they are truly unused
 * they should be removed after confirming no callers depend on them.
 *
 * @see \MobilityWork\Application\Handler\CreateCustomerTicketHandler
 */
final readonly class CreateCustomerTicketCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $message,
        public string $languageName,
        public ?string $phoneNumber = null,
        public ?string $reservationNumber = null,
        public ?int $hotelId = null,
        /** @deprecated Collected but unused — kept for API compatibility */
        public ?string $gender = null,
    ) {
    }
}
