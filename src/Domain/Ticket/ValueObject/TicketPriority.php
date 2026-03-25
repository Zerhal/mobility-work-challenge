<?php

declare(strict_types=1);

namespace MobilityWork\Domain\Ticket\ValueObject;

enum TicketPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';
}
