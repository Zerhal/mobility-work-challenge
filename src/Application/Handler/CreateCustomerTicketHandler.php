<?php

declare(strict_types=1);

namespace MobilityWork\Application\Handler;

use MobilityWork\Application\Command\CreateCustomerTicketCommand;
use MobilityWork\Application\Port\TicketEnricherInterface;
use MobilityWork\Domain\Ticket\Port\TicketRepositoryInterface;
use MobilityWork\Domain\Ticket\ValueObject\CustomFieldId;
use MobilityWork\Domain\Ticket\ValueObject\TicketId;
use MobilityWork\Domain\Ticket\ValueObject\TicketPriority;
use MobilityWork\Domain\Ticket\ValueObject\TicketSubject;
use MobilityWork\Domain\Ticket\ValueObject\TicketType;
use MobilityWork\Domain\User\Port\UserRepositoryInterface;
use MobilityWork\Domain\User\ValueObject\ContactName;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Handles the creation of a customer support ticket.
 *
 * Responsibilities:
 *  - Orchestrate user upsert and ticket creation via injected ports
 *  - Build the custom field payload using named constants
 *  - Delegate AI enrichment (priority, tags) to the enricher port
 *  - Return the created ticket's ID to the caller
 *
 * This class has NO knowledge of Zendesk, HTTP, or any SDK. It depends
 * only on interfaces — making it fully unit-testable with in-memory fakes.
 */
final class CreateCustomerTicketHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TicketRepositoryInterface $ticketRepository,
        private readonly TicketEnricherInterface $enricher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreateCustomerTicketCommand $command): TicketId
    {
        $name = new ContactName($command->firstName, $command->lastName);

        // Upsert the Zendesk user, resolving phone from reservation if absent.
        $userId = $this->userRepository->upsert([
            'email' => $command->email,
            'name' => (string) $name,
            'phone' => $command->phoneNumber ?? '',
            'role' => 'end-user',
        ]);

        $subject = new TicketSubject($command->message);
        $priority = $this->resolvePriority($command->message);
        $tags = $this->enricher->suggestTags($command->message);

        $customFields = $this->buildCustomFields($command);

        $this->logger->info('Creating customer ticket', [
            'email' => $command->email,
            'priority' => $priority->value,
            'tags' => $tags,
        ]);

        return $this->ticketRepository->create([
            'requester_id' => $userId,
            'subject' => (string) $subject,
            'comment' => ['body' => $command->message],
            'priority' => $priority->value,
            'type' => 'question',
            'status' => 'new',
            'tags' => $tags,
            'custom_fields' => $customFields,
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function buildCustomFields(CreateCustomerTicketCommand $command): array
    {
        $fields = [
            CustomFieldId::TICKET_TYPE => TicketType::Customer->value,
            CustomFieldId::RESERVATION_REF => $command->reservationNumber,
            CustomFieldId::LANGUAGE => $command->languageName,
        ];

        // Hotel fields are populated by the caller when hotelId is resolved
        // upstream (e.g. from the reservation). The handler stays lean —
        // it does not reach into repositories for hotel/reservation data.
        // Pass enriched hotel data via a dedicated DTO if needed.

        /** @var array<string, string|null> $filtered */
        $filtered = array_filter(
            $fields,
            static fn (mixed $v): bool => $v !== null,
        );

        return $filtered;
    }

    private function resolvePriority(string $message): TicketPriority
    {
        try {
            return $this->enricher->suggestPriority($message);
        } catch (Throwable $e) {
            // AI enrichment is advisory — never block ticket creation
            $this->logger->warning('Priority enrichment failed, falling back to normal.', [
                'error' => $e->getMessage(),
            ]);

            return TicketPriority::Normal;
        }
    }
}
