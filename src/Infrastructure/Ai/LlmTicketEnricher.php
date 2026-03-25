<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai;

use JsonException;
use MobilityWork\Application\Port\TicketEnricherInterface;
use MobilityWork\Domain\Ticket\ValueObject\TicketPriority;
use MobilityWork\Infrastructure\Ai\Provider\LlmClientInterface;
use MobilityWork\Infrastructure\Ai\Provider\LlmProviderException;
use Psr\Log\LoggerInterface;

/**
 * LLM-powered implementation of TicketEnricherInterface.
 *
 * This class is completely provider-agnostic: it depends only on
 * LlmClientInterface. The actual LLM (Anthropic, Ollama, OpenAI…)
 * is injected at construction time by the TicketEnricherFactory.
 *
 * On any LLM failure, all methods return safe defaults so that ticket
 * creation is NEVER blocked by AI unavailability.
 */
final class LlmTicketEnricher implements TicketEnricherInterface
{
    public function __construct(
        private readonly LlmClientInterface $llmClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function suggestPriority(string $message): TicketPriority
    {
        $json = $this->callJson(
            system: <<<PROMPT
                You are a support ticket triage assistant.
                Analyse the user message and return ONLY a JSON object: {"priority": "low"|"normal"|"high"|"urgent"}
                Escalate to "high" for billing issues, access problems, data loss.
                Escalate to "urgent" for system outages or security incidents.
                No explanation. Only valid JSON.
                PROMPT,
            user: $message,
        );

        return TicketPriority::tryFrom($json['priority'] ?? '') ?? TicketPriority::Normal;
    }

    public function generateSubject(string $message, string $locale = 'en'): string
    {
        $json = $this->callJson(
            system: <<<PROMPT
                You are a support ticket assistant.
                Given a user message, generate a concise ticket subject (max 50 characters).
                Return ONLY a JSON object: {"subject": "..."}
                Language: {$locale}
                No explanation. Only valid JSON.
                PROMPT,
            user: $message,
        );

        $subject = $json['subject'] ?? null;

        return \is_string($subject) && trim($subject) !== ''
            ? mb_substr(trim($subject), 0, 50)
            : mb_substr($message, 0, 50);
    }

    /**
     * @return string[]
     */
    public function suggestTags(string $message): array
    {
        $json = $this->callJson(
            system: <<<PROMPT
                You are a support ticket assistant.
                Extract 2 to 5 relevant routing tags from the user message.
                Return ONLY a JSON object: {"tags": ["tag1", "tag2"]}
                Tags must be lowercase with hyphens, no spaces.
                No explanation. Only valid JSON.
                PROMPT,
            user: $message,
        );

        $tags = $json['tags'] ?? [];

        return \is_array($tags)
            ? array_values(array_filter($tags, 'is_string'))
            : [];
    }

    /**
     * Calls the LLM and parses the response as JSON.
     *
     * @return array<string, mixed>
     */
    private function callJson(string $system, string $user): array
    {
        try {
            $raw = $this->llmClient->complete($system, $user);

            // Strip accidental markdown code fences
            $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));

            if ($clean === null) {
                return [];
            }

            return json_decode($clean, true, flags: \JSON_THROW_ON_ERROR);
        } catch (LlmProviderException $e) {
            $this->logger->warning('LLM enrichment failed — using defaults.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        } catch (JsonException $e) {
            $this->logger->warning('LLM returned unparseable JSON — using defaults.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
