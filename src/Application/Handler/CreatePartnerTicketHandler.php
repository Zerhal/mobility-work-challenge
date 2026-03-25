<?php

declare(strict_types=1);

namespace MobilityWork\Application\Handler;

use MobilityWork\Application\Command\CreatePartnerTicketCommand;
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

final class CreatePartnerTicketHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TicketRepositoryInterface $ticketRepository,
        private readonly TicketEnricherInterface $enricher,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(CreatePartnerTicketCommand $command): TicketId
    {
        $name = new ContactName($command->firstName, $command->lastName);

        $userId = $this->userRepository->upsert([
            'email' => $command->email,
            'name' => (string) $name,
            'phone' => $command->phoneNumber ?? '',
            'role' => 'end-user',
        ]);

        $subject = new TicketSubject($command->message);
        $priority = $this->tryEnrichPriority($command->message);
        $tags = $this->enricher->suggestTags($command->message);

        $customFields = [
            CustomFieldId::TICKET_TYPE => TicketType::Partner->value,
            CustomFieldId::LANGUAGE => $command->languageName,
        ];

        $this->logger->info('Creating partner ticket', ['email' => $command->email]);

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

    private function tryEnrichPriority(string $message): TicketPriority
    {
        try {
            return $this->enricher->suggestPriority($message);
        } catch (Throwable) {
            return TicketPriority::Normal;
        }
    }
}
