<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai;

use InvalidArgumentException;

/**
 * Typed Value Object for AI configuration.
 *
 * Centralises all AI-related settings and validates them at construction
 * time (fail fast). Constructed once and injected into TicketEnricherFactory.
 *
 * Reading from environment variables:
 *
 * @see AiConfig::fromEnvironment()
 *
 * Reading from a Symfony config array:
 * @see AiConfig::fromArray()
 *
 * Available environment variables:
 * ┌──────────────────────────┬───────────────────────────────────────────────┐
 * │ Variable                 │ Description                                   │
 * ├──────────────────────────┼───────────────────────────────────────────────┤
 * │ AI_ENABLED               │ true / false — master switch (default: false) │
 * │ AI_PROVIDER              │ anthropic | ollama                            │
 * │ AI_ANTHROPIC_API_KEY     │ Required when provider = anthropic            │
 * │ AI_ANTHROPIC_MODEL       │ Claude model slug (optional)                  │
 * │ AI_OLLAMA_BASE_URL       │ Ollama server URL (optional)                  │
 * │ AI_OLLAMA_MODEL          │ Ollama model name (optional)                  │
 * │ AI_MAX_TOKENS            │ Max completion tokens (optional, default 256) │
 * └──────────────────────────┴───────────────────────────────────────────────┘
 */
final class AiConfig
{
    /** @param array<string, string> $options Provider-specific options */
    public function __construct(
        public readonly bool $enabled,
        public readonly string $provider,
        private readonly array $options = [],
    ) {
    }

    public static function fromEnvironment(): self
    {
        $enabled = filter_var(getenv('AI_ENABLED') ?: 'false', \FILTER_VALIDATE_BOOLEAN);

        return new self(
            enabled: $enabled,
            provider: getenv('AI_PROVIDER') ?: 'null',
            options: [
                'anthropic_api_key' => getenv('AI_ANTHROPIC_API_KEY') ?: '',
                'anthropic_model' => getenv('AI_ANTHROPIC_MODEL') ?: '',
                'ollama_base_url' => getenv('AI_OLLAMA_BASE_URL') ?: '',
                'ollama_model' => getenv('AI_OLLAMA_MODEL') ?: '',
                'max_tokens' => getenv('AI_MAX_TOKENS') ?: '256',
            ],
        );
    }

    /** @param array<string, mixed> $config */
    public static function fromArray(array $config): self
    {
        return new self(
            enabled: (bool) ($config['enabled'] ?? false),
            provider: (string) ($config['provider'] ?? 'null'),
            options: array_map('strval', $config['options'] ?? []),
        );
    }

    public function getOption(string $key, string $default = ''): string
    {
        return $this->options[$key] ?? $default;
    }

    public function requireOption(string $key): string
    {
        $value = $this->options[$key] ?? '';
        if (trim($value) === '') {
            throw new InvalidArgumentException(
                \sprintf('AI config option "%s" is required for provider "%s" but is not set.', $key, $this->provider),
            );
        }

        return $value;
    }
}
