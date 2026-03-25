<?php

declare(strict_types=1);

namespace MobilityWork\Tests\Unit\Application\Handler;

use MobilityWork\Application\Command\CreateCustomerTicketCommand;
use MobilityWork\Application\Handler\CreateCustomerTicketHandler;
use MobilityWork\Domain\Ticket\ValueObject\CustomFieldId;
use MobilityWork\Domain\Ticket\ValueObject\TicketPriority;
use MobilityWork\Domain\Ticket\ValueObject\TicketType;
use MobilityWork\Infrastructure\Ai\NullTicketEnricher;
use MobilityWork\Tests\Doubles\InMemoryTicketRepository;
use MobilityWork\Tests\Doubles\InMemoryUserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(CreateCustomerTicketHandler::class)]
final class CreateCustomerTicketHandlerTest extends TestCase
{
    private InMemoryTicketRepository $ticketRepository;
    private InMemoryUserRepository $userRepository;
    private CreateCustomerTicketHandler $handler;

    protected function setUp(): void
    {
        $this->ticketRepository = new InMemoryTicketRepository();
        $this->userRepository = new InMemoryUserRepository();

        $this->handler = new CreateCustomerTicketHandler(
            userRepository: $this->userRepository,
            ticketRepository: $this->ticketRepository,
            enricher: new NullTicketEnricher(),
            logger: new NullLogger(),
        );
    }

    protected function tearDown(): void
    {
        $this->ticketRepository->reset();
        $this->userRepository->reset();
    }

    public function testHandlerCreatesExactlyOneTicket(): void
    {
        $command = $this->makeCommand();
        $this->handler->__invoke($command);

        $this->assertSame(1, $this->ticketRepository->count());
    }

    public function testHandlerReturnsTicketId(): void
    {
        $command = $this->makeCommand();
        $ticketId = $this->handler->__invoke($command);

        $this->assertGreaterThan(0, $ticketId->getValue());
    }

    public function testTicketSubjectIsTruncatedAt50Chars(): void
    {
        $longMessage = str_repeat('a', 100);
        $command = $this->makeCommand(message: $longMessage);
        $this->handler->__invoke($command);

        $ticket = $this->ticketRepository->getLastCreatedTicket();
        $this->assertNotNull($ticket);
        $this->assertStringEndsWith('...', $ticket['subject']);
        $this->assertSame(53, mb_strlen($ticket['subject']));
    }

    public function testTicketHasCorrectCustomFieldType(): void
    {
        $this->handler->__invoke($this->makeCommand());

        $ticket = $this->ticketRepository->getLastCreatedTicket();
        $this->assertNotNull($ticket);
        $customFields = $ticket['custom_fields'];

        $this->assertSame(TicketType::Customer->value, $customFields[CustomFieldId::TICKET_TYPE]);
    }

    public function testTicketHasDefaultNormalPriority(): void
    {
        $this->handler->__invoke($this->makeCommand());

        $ticket = $this->ticketRepository->getLastCreatedTicket();
        $this->assertNotNull($ticket);
        $this->assertSame(TicketPriority::Normal->value, $ticket['priority']);
    }

    public function testUpsertedUserHasFormattedName(): void
    {
        $command = $this->makeCommand(firstName: 'jean', lastName: 'dupont');
        $this->handler->__invoke($command);

        $user = $this->userRepository->getLastUpsertedUser();
        $this->assertNotNull($user);
        $this->assertSame('jean DUPONT', $user['name']);
    }

    public function testUpsertedUserHasCorrectEmail(): void
    {
        $command = $this->makeCommand(email: 'test@example.com');
        $this->handler->__invoke($command);

        $user = $this->userRepository->getLastUpsertedUser();
        $this->assertNotNull($user);
        $this->assertSame('test@example.com', $user['email']);
    }

    public function testTicketCommentBodyMatchesMessage(): void
    {
        $message = 'My machine is broken and I need help urgently.';
        $this->handler->__invoke($this->makeCommand(message: $message));

        $ticket = $this->ticketRepository->getLastCreatedTicket();
        $this->assertNotNull($ticket);
        $this->assertSame($message, $ticket['comment']['body']);
    }

    public function testTicketStatusIsNew(): void
    {
        $this->handler->__invoke($this->makeCommand());

        $ticket = $this->ticketRepository->getLastCreatedTicket();
        $this->assertNotNull($ticket);
        $this->assertSame('new', $ticket['status']);
    }

    public function testReservationNumberAppearsInCustomFields(): void
    {
        $command = $this->makeCommand(reservationNumber: 'RES-12345');
        $this->handler->__invoke($command);

        $ticket = $this->ticketRepository->getLastCreatedTicket();
        $this->assertNotNull($ticket);
        $this->assertSame('RES-12345', $ticket['custom_fields'][CustomFieldId::RESERVATION_REF]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeCommand(
        string $firstName = 'Alice',
        string $lastName = 'Martin',
        string $email = 'alice@example.com',
        string $message = 'I need help with my maintenance task.',
        string $languageName = 'French',
        ?string $phoneNumber = null,
        ?string $reservationNumber = null,
    ): CreateCustomerTicketCommand {
        return new CreateCustomerTicketCommand(
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            message: $message,
            languageName: $languageName,
            phoneNumber: $phoneNumber,
            reservationNumber: $reservationNumber,
        );
    }
}
