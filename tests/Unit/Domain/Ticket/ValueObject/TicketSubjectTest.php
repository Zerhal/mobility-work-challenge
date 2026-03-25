<?php

declare(strict_types=1);

namespace MobilityWork\Tests\Unit\Domain\Ticket\ValueObject;

use InvalidArgumentException;
use MobilityWork\Domain\Ticket\ValueObject\TicketSubject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TicketSubject::class)]
final class TicketSubjectTest extends TestCase
{
    public function testShortMessageIsReturnedAsIs(): void
    {
        $subject = new TicketSubject('Hello, I need help.');
        $this->assertSame('Hello, I need help.', $subject->getValue());
    }

    public function testExactly50CharactersIsNotTruncated(): void
    {
        $message = str_repeat('a', 50);
        $subject = new TicketSubject($message);
        $this->assertSame($message, $subject->getValue());
    }

    public function testMessageLongerThan50CharsIsTruncatedWithEllipsis(): void
    {
        $message = str_repeat('a', 51);
        $subject = new TicketSubject($message);
        $this->assertSame(str_repeat('a', 50) . '...', $subject->getValue());
    }

    public function testLongMessageProducesCorrectedLength(): void
    {
        $message = str_repeat('x', 200);
        $subject = new TicketSubject($message);
        // 50 chars + '...' = 53 total
        $this->assertSame(53, mb_strlen($subject->getValue()));
    }

    public function testEmptyMessageThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TicketSubject('');
    }

    public function testWhitespaceOnlyMessageThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TicketSubject('   ');
    }

    public function testToStringReturnsSameAsGetValue(): void
    {
        $subject = new TicketSubject('Test message');
        $this->assertSame($subject->getValue(), (string) $subject);
    }

    public function testMultibyteCharactersAreHandledCorrectly(): void
    {
        // 51 multibyte characters (é = 2 bytes in UTF-8 but 1 mb_strlen char)
        $message = str_repeat('é', 51);
        $subject = new TicketSubject($message);
        $this->assertStringEndsWith('...', $subject->getValue());
        $this->assertSame(50 + 3, mb_strlen($subject->getValue()));
    }
}
