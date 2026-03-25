<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Zendesk;

use MobilityWork\Domain\User\Port\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * Zendesk adapter for UserRepositoryInterface.
 *
 * Isolates the createOrUpdate call so that both TicketRepository and
 * UserRepository can be stubbed independently in tests.
 */
final class ZendeskUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly ZendeskClientFactory $clientFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $userData
     *
     * @throws RuntimeException
     */
    public function upsert(array $userData): int
    {
        try {
            $client = $this->clientFactory->create();
            $response = $client->users()->createOrUpdate($userData);

            if (! isset($response->user->id)) {
                throw new UnexpectedValueException('Zendesk response missing user.id');
            }

            return (int) $response->user->id;
        } catch (Throwable $e) {
            $this->logger->error('Zendesk user upsert failed.', [
                'email' => $userData['email'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                \sprintf('Failed to upsert Zendesk user: %s', $e->getMessage()),
                0,
                $e,
            );
        }
    }
}
