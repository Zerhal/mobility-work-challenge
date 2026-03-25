<?php

declare(strict_types=1);

namespace MobilityWork\Application\Port;

use MobilityWork\Domain\Ticket\ValueObject\TicketPriority;

/**
 * Port (outbound) — AI-powered ticket enrichment.
 *
 * This port decouples the application layer from any specific AI provider.
 * The production adapter calls the Anthropic API; the NullTicketEnricher
 * is used in test and non-AI environments.
 *
 * Enrichment is purely advisory — the handler always falls back to defaults
 * if enrichment fails, so this never becomes a critical path dependency.
 */
interface TicketEnricherInterface
{
    /**
     * Analyses a message and returns an enriched priority.
     *
     * The AI may detect urgency signals (e.g. "urgent", "not working",
     * "cannot access", "billing error") and escalate beyond 'normal'.
     */
    public function suggestPriority(string $message): TicketPriority;

    /**
     * Generates a concise, structured subject line from a free-form message.
     *
     * Useful when the user has not provided an explicit subject, or when
     * the message starts with informal preamble ("Hello, I have a problem…").
     */
    public function generateSubject(string $message, string $locale = 'en'): string;

    /**
     * Extracts suggested tags from message content.
     *
     * @return string[]
     */
    public function suggestTags(string $message): array;
}
