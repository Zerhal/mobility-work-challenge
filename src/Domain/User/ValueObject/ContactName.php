<?php

declare(strict_types=1);

namespace MobilityWork\Domain\User\ValueObject;

/**
 * Encapsulates the display-name formatting rule:
 *   "{firstName} {LASTNAME}"
 *
 * The original code repeated `$firstName.' '.strtoupper($lastName)` four
 * times. Centralising this in a VO means the rule is named, tested once,
 * and impossible to forget.
 */
final readonly class ContactName
{
    private string $value;

    public function __construct(
        string $firstName,
        string $lastName,
    ) {
        $this->value = trim($firstName) . ' ' . mb_strtoupper(trim($lastName));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
