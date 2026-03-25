<?php

declare(strict_types=1);

namespace MobilityWork\Tests\Unit\Domain\User\ValueObject;

use MobilityWork\Domain\User\ValueObject\ContactName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactName::class)]
final class ContactNameTest extends TestCase
{
    public function testLastNameIsUppercased(): void
    {
        $name = new ContactName('Jean', 'dupont');
        $this->assertSame('Jean DUPONT', $name->getValue());
    }

    public function testAlreadyUppercaseLastNameRemainsCorrect(): void
    {
        $name = new ContactName('Jean', 'DUPONT');
        $this->assertSame('Jean DUPONT', $name->getValue());
    }

    public function testWhitespaceIsTrimmed(): void
    {
        $name = new ContactName('  Jean  ', '  Dupont  ');
        $this->assertSame('Jean DUPONT', $name->getValue());
    }

    public function testToStringMatchesGetValue(): void
    {
        $name = new ContactName('Alice', 'Martin');
        $this->assertSame($name->getValue(), (string) $name);
    }

    public function testMultibyteFirstName(): void
    {
        $name = new ContactName('Élodie', 'Müller');
        $this->assertSame('Élodie MÜLLER', $name->getValue());
    }
}
