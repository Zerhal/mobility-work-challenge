<?php

declare(strict_types=1);

namespace MobilityWork\Tests\Doubles;

use MobilityWork\Domain\User\Port\UserRepositoryInterface;

/**
 * In-memory test double for UserRepositoryInterface.
 */
final class InMemoryUserRepository implements UserRepositoryInterface
{
    private static int $nextId = 100;

    /** @var array<int, array<string, mixed>> */
    private array $users = [];

    public function upsert(array $userData): int
    {
        $id = self::$nextId++;
        $this->users[$id] = $userData;

        return $id;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLastUpsertedUser(): ?array
    {
        if (empty($this->users)) {
            return null;
        }

        return end($this->users);
    }

    public function reset(): void
    {
        $this->users = [];
        self::$nextId = 100;
    }
}
