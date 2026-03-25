<?php

declare(strict_types=1);

namespace MobilityWork\Application\Command;

/**
 * CQRS Command — expresses the intent to open a hotel registration ticket.
 *
 * @see \MobilityWork\Application\Handler\CreateHotelTicketHandler
 */
final readonly class CreateHotelTicketCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $message,
        public string $languageName,
        public string $hotelName,
        public ?string $phoneNumber = null,
        public ?string $city = null,
        public ?string $website = null,
        public ?string $country = null,
        public ?string $gender = null,
    ) {
    }
}
