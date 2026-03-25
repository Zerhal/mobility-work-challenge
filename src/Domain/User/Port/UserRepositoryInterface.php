<?php

declare(strict_types=1);

namespace MobilityWork\Domain\User\Port;

/**
 * Port (outbound) — the domain's expectation of a user upsert mechanism.
 *
 * Zendesk's createOrUpdate pattern is abstracted here so that handlers
 * never depend on SDK specifics.
 */
interface UserRepositoryInterface
{
    /**
     * Creates or updates a user and returns their Zendesk user ID.
     *
     * @param array<string, mixed> $userData
     */
    public function upsert(array $userData): int;
}
