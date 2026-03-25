<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai\Provider;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * Ollama adapter for LlmClientInterface.
 *
 * Ollama lets you run models locally (Llama 3, Mistral, Phi…) with a
 * simple REST API. No API key required — just a running Ollama instance.
 *
 * Configuration (via env or Symfony config):
 *   AI_PROVIDER=ollama
 *   AI_OLLAMA_BASE_URL=http://localhost:11434   (optional, has default)
 *   AI_OLLAMA_MODEL=llama3                      (optional, has default)
 *
 * @see https://ollama.com
 */
final class OllamaLlmClient implements LlmClientInterface
{
    private const DEFAULT_BASE_URL = 'http://localhost:11434';
    private const DEFAULT_MODEL = 'llama3';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl = self::DEFAULT_BASE_URL,
        private readonly string $model = self::DEFAULT_MODEL,
    ) {
    }

    public function complete(string $systemPrompt, string $userMessage): string
    {
        try {
            $response = $this->httpClient->request('POST', rtrim($this->baseUrl, '/') . '/api/chat', [
                'json' => [
                    'model' => $this->model,
                    'stream' => false,
                    'messages' => [
                        ['role' => 'system',  'content' => $systemPrompt],
                        ['role' => 'user',    'content' => $userMessage],
                    ],
                ],
            ]);

            $body = $response->toArray();

            return $body['message']['content'] ?? '';
        } catch (Throwable $e) {
            throw LlmProviderException::fromThrowable('ollama', $e);
        }
    }
}
