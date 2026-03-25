<?php

declare(strict_types=1);

namespace MobilityWork\Application\Command;

/**
 * CQRS Command — expresses the intent to open a press enquiry ticket.
 *
 * @see \MobilityWork\Application\Handler\CreatePressTicketHandler
 */
final readonly class CreatePressTicketCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $message,
        public string $languageName,
        public ?string $phoneNumber = null,
        public ?string $city = null,
        public ?string $media = null,
        public ?string $country = null,
        public ?string $gender = null,
    ) {
    }
}
