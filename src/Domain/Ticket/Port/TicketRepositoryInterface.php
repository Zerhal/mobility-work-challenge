<?php

declare(strict_types=1);

namespace MobilityWork\Domain\Ticket\Port;

use MobilityWork\Domain\Ticket\ValueObject\TicketId;

/**
 * Port (outbound) — the domain's expectation of a ticket persistence mechanism.
 *
 * In hexagonal architecture terms this is the "driven" port. The Zendesk SDK
 * adapter and the in-memory test fake both implement this interface, making
 * all application logic completely independent of the external ticketing system.
 */
interface TicketRepositoryInterface
{
    /**
     * @param array<string, mixed> $payload Normalised ticket data
     *
     * @throws \MobilityWork\Domain\Ticket\Exception\TicketCreationFailedException
     */
    public function create(array $payload): TicketId;
}
