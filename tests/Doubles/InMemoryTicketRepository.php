<?php

declare(strict_types=1);

namespace MobilityWork\Tests\Doubles;

use MobilityWork\Domain\Ticket\Port\TicketRepositoryInterface;
use MobilityWork\Domain\Ticket\ValueObject\TicketId;

/**
 * In-memory test double for TicketRepositoryInterface.
 *
 * Stores created ticket payloads in memory so unit tests can assert
 * on what was actually sent — without any HTTP calls.
 */
final class InMemoryTicketRepository implements TicketRepositoryInterface
{
    private static int $nextId = 1;

    /** @var array<int, array<string, mixed>> */
    private array $tickets = [];

    public function create(array $payload): TicketId
    {
        $id = self::$nextId++;
        $this->tickets[$id] = $payload;

        return new TicketId($id);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLastCreatedTicket(): ?array
    {
        if (empty($this->tickets)) {
            return null;
        }

        return end($this->tickets);
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->tickets;
    }

    public function count(): int
    {
        return \count($this->tickets);
    }

    public function reset(): void
    {
        $this->tickets = [];
        self::$nextId = 1;
    }
}
