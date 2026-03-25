<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Ai\Provider;

/**
 * Provider-agnostic LLM client interface.
 *
 * Any LLM backend (Anthropic, Ollama/Llama, OpenAI, Mistral…) must implement
 * this single method. The rest of the AI layer depends only on this contract,
 * never on a specific vendor SDK.
 *
 * The contract is intentionally minimal:
 *  - Send a system prompt + a user message
 *  - Receive a plain text completion
 *
 * JSON parsing, error handling and retry logic live in the caller
 * (LlmTicketEnricher), not here.
 */
interface LlmClientInterface
{
    /**
     * Sends a prompt to the LLM and returns the raw text completion.
     *
     * @throws LlmProviderException
     */
    public function complete(string $systemPrompt, string $userMessage): string;
}
