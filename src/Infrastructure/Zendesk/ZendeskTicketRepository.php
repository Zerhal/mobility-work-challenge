<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Zendesk;

use MobilityWork\Domain\Ticket\Exception\TicketCreationFailedException;
use MobilityWork\Domain\Ticket\Port\TicketRepositoryInterface;
use MobilityWork\Domain\Ticket\ValueObject\TicketId;
use Psr\Log\LoggerInterface;
use Throwable;
use UnexpectedValueException;

/**
 * Zendesk adapter for TicketRepositoryInterface.
 *
 * This is the only class in the codebase that knows about the Zendesk SDK.
 * It translates domain-level calls into HTTP requests and wraps SDK
 * exceptions into the domain exception TicketCreationFailedException,
 * so the application layer never leaks infrastructure concerns.
 *
 * The original code had zero error handling in 3/4 methods. Here every
 * create call is wrapped and errors are always logged with meaningful context.
 */
final class ZendeskTicketRepository implements TicketRepositoryInterface
{
    public function __construct(
        private readonly ZendeskClientFactory $clientFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws TicketCreationFailedException
     */
    public function create(array $payload): TicketId
    {
        try {
            $client = $this->clientFactory->create();
            $response = $client->tickets()->create($payload);

            if (! isset($response->ticket->id)) {
                throw new UnexpectedValueException('Zendesk response missing ticket.id');
            }

            $ticketId = new TicketId((int) $response->ticket->id);

            $this->logger->info('Zendesk ticket created.', ['ticket_id' => $ticketId->getValue()]);

            return $ticketId;
        } catch (Throwable $e) {
            $this->logger->error('Zendesk ticket creation failed.', [
                'error' => $e->getMessage(),
                'payload' => array_diff_key($payload, ['comment' => '']), // omit message body from logs
            ]);

            throw TicketCreationFailedException::withReason($e->getMessage(), $e);
        }
    }
}
