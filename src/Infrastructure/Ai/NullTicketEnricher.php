<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai;

use MobilityWork\Application\Port\TicketEnricherInterface;
use MobilityWork\Domain\Ticket\ValueObject\TicketPriority;

/**
 * Null Object implementation of TicketEnricherInterface.
 *
 * Used in:
 *  - Unit tests (avoids HTTP calls)
 *  - Environments where ANTHROPIC_API_KEY is not configured
 *
 * Applying the Null Object pattern means handlers never need to
 * null-check the enricher, keeping orchestration code clean.
 */
final class NullTicketEnricher implements TicketEnricherInterface
{
    public function suggestPriority(string $message): TicketPriority
    {
        return TicketPriority::Normal;
    }

    public function generateSubject(string $message, string $locale = 'en'): string
    {
        return mb_substr($message, 0, 50);
    }

    public function suggestTags(string $message): array
    {
        return [];
    }
}
