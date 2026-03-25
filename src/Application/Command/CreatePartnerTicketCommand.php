<?php

declare(strict_types=1);

namespace MobilityWork\Application\Command;

/**
 * CQRS Command — expresses the intent to open a partner enquiry ticket.
 *
 * @see \MobilityWork\Application\Handler\CreatePartnerTicketHandler
 */
final readonly class CreatePartnerTicketCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $message,
        public string $languageName,
        public ?string $phoneNumber = null,
        public ?string $gender = null,
    ) {
    }
}
