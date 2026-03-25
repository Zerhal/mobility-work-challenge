<?php

declare(strict_types=1);

namespace MobilityWork\Domain\Ticket\ValueObject;

/**
 * Maps human-readable field names to their Zendesk custom field IDs.
 *
 * The original code used raw numeric strings ('80924888', '80531327', …)
 * with no indication of their meaning. A reader had to cross-reference
 * the Zendesk admin panel to understand the data model.
 *
 * These constants make the intent explicit and provide a single place to
 * update if Zendesk field IDs ever change (e.g. after a sandbox migration).
 */
final class CustomFieldId
{
    /** Ticket origin type: customer | hotel | press | partner */
    public const TICKET_TYPE = '80924888';

    /** Booking / reservation reference number */
    public const RESERVATION_REF = '80531327';

    /** Hotel contact e-mail address */
    public const HOTEL_EMAIL = '80531267';

    /** Hotel name */
    public const HOTEL_NAME = '80918668';

    /** Hotel address / city */
    public const HOTEL_ADDRESS = '80918648';

    /** Room name and type */
    public const ROOM_NAME = '80531287';

    /** Booked date (YYYY-MM-DD) */
    public const BOOKED_DATE = '80531307';

    /** Room price with currency code */
    public const ROOM_PRICE = '80924568';

    /** Booked time slot (HH:MM - HH:MM) */
    public const BOOKED_TIME = '80918728';

    /** User language name */
    public const LANGUAGE = '80918708';

    /** Prevent instantiation — this is a constants bag */
    private function __construct()
    {
    }
}
