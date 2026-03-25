<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai\Provider;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * Anthropic Claude adapter for LlmClientInterface.
 *
 * Configuration (via env or Symfony config):
 *   AI_PROVIDER=anthropic
 *   AI_ANTHROPIC_API_KEY=sk-ant-...
 *   AI_ANTHROPIC_MODEL=claude-sonnet-4-20250514   (optional, has default)
 *   AI_MAX_TOKENS=256                              (optional, has default)
 */
final class AnthropicLlmClient implements LlmClientInterface
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const DEFAULT_MODEL = 'claude-sonnet-4-20250514';
    private const DEFAULT_TOKENS = 256;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model = self::DEFAULT_MODEL,
        private readonly int $maxTokens = self::DEFAULT_TOKENS,
    ) {
    }

    public function complete(string $systemPrompt, string $userMessage): string
    {
        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'max_tokens' => $this->maxTokens,
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                ],
            ]);

            $body = $response->toArray();

            return $body['content'][0]['text'] ?? '';
        } catch (Throwable $e) {
            throw LlmProviderException::fromThrowable('anthropic', $e);
        }
    }
}
