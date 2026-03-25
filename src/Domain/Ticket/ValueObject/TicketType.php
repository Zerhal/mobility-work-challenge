<?php

declare(strict_types=1);

namespace MobilityWork\Domain\Ticket\ValueObject;

/**
 * Represents the origin/type of a support ticket.
 *
 * Using a PHP 8.1 backed enum eliminates the magic strings
 * ('customer', 'hotel', 'press', 'partner') scattered across
 * the original service and makes exhaustiveness checkable at
 * compile time via match expressions.
 */
enum TicketType: string
{
    case Customer = 'customer';
    case Hotel = 'hotel';
    case Press = 'press';
    case Partner = 'partner';
}
