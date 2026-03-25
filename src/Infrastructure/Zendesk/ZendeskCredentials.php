<?php

declare(strict_types=1);

namespace MobilityWork\Infrastructure\Zendesk;

use InvalidArgumentException;
use RuntimeException;

/**
 * Groups Zendesk connection credentials into a single typed Value Object.
 *
 * The original code accessed credentials via:
 *   $this->getServiceManager()->get('Config')['zendesk']['subdomain']
 *
 * — repeated 4 times, coupling every method to the service locator and
 * making credentials impossible to validate upfront.
 *
 * This VO validates at construction time (fail fast), is injected once
 * into ZendeskClientFactory, and never leaks into application code.
 */
final readonly class ZendeskCredentials
{
    public function __construct(
        public string $subdomain,
        public string $username,
        public string $token,
    ) {
        if (trim($subdomain) === '') {
            throw new InvalidArgumentException('Zendesk subdomain cannot be empty.');
        }
        if (trim($username) === '') {
            throw new InvalidArgumentException('Zendesk username cannot be empty.');
        }
        if (trim($token) === '') {
            throw new InvalidArgumentException('Zendesk API token cannot be empty.');
        }
    }

    public static function fromEnvironment(): self
    {
        return new self(
            subdomain: self::requireEnv('ZENDESK_SUBDOMAIN'),
            username: self::requireEnv('ZENDESK_USERNAME'),
            token: self::requireEnv('ZENDESK_TOKEN'),
        );
    }

    private static function requireEnv(string $key): string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            throw new RuntimeException(\sprintf('Required environment variable "%s" is not set.', $key));
        }

        return $value;
    }
}
