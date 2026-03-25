<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai;

use InvalidArgumentException;
use MobilityWork\Application\Port\TicketEnricherInterface;
use MobilityWork\Infrastructure\Ai\Provider\AnthropicLlmClient;
use MobilityWork\Infrastructure\Ai\Provider\OllamaLlmClient;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Reads AI configuration and returns the appropriate TicketEnricher.
 *
 * This is the single place that knows which LLM provider to instantiate.
 * All other classes depend only on TicketEnricherInterface — they never
 * see this factory.
 *
 * Supported providers (AI_PROVIDER env var):
 *   - "anthropic"  → Anthropic Claude API  (requires AI_ANTHROPIC_API_KEY)
 *   - "ollama"     → Local Ollama instance  (requires a running Ollama server)
 *   - "null" / ""  → NullTicketEnricher     (AI disabled, safe default)
 *
 * Adding a new provider only requires:
 *   1. Implementing LlmClientInterface
 *   2. Adding one case in the match below
 *
 * @see Provider\LlmClientInterface
 */
final class TicketEnricherFactory
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function create(AiConfig $config): TicketEnricherInterface
    {
        if (! $config->enabled) {
            return new NullTicketEnricher();
        }

        $llmClient = match ($config->provider) {
            'anthropic' => new AnthropicLlmClient(
                httpClient: $this->httpClient,
                apiKey: $config->requireOption('anthropic_api_key'),
                model: $config->getOption('anthropic_model', 'claude-sonnet-4-20250514'),
                maxTokens: (int) $config->getOption('max_tokens', '256'),
            ),
            'ollama' => new OllamaLlmClient(
                httpClient: $this->httpClient,
                baseUrl: $config->getOption('ollama_base_url', 'http://localhost:11434'),
                model: $config->getOption('ollama_model', 'llama3'),
            ),
            default => throw new InvalidArgumentException(
                \sprintf(
                    'Unknown AI provider "%s". Supported: anthropic, ollama. Set AI_ENABLED=false to disable.',
                    $config->provider,
                ),
            ),
        };

        return new LlmTicketEnricher($llmClient, $this->logger);
    }
}
