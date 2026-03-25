<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Zendesk;

use Zendesk\API\HttpClient as ZendeskAPI;

/**
 * Constructs and authenticates a ZendeskAPI client.
 *
 * The original code copy-pasted client instantiation and authentication
 * four times. This factory centralises that logic and is the single place
 * that knows about the Zendesk SDK's initialisation API.
 *
 * Injecting this factory (rather than ZendeskAPI directly) allows tests
 * to swap in a mock factory that returns a stubbed client.
 */
final class ZendeskClientFactory
{
    public function __construct(
        private readonly ZendeskCredentials $credentials,
    ) {
    }

    public function create(): ZendeskAPI
    {
        $client = new ZendeskAPI($this->credentials->subdomain);
        $client->setAuth('basic', [
            'username' => $this->credentials->username,
            'token' => $this->credentials->token,
        ]);

        return $client;
    }
}
