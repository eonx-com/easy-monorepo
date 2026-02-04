<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\Event;

/**
 * This event is guaranteed to be dispatched after an envelope is dispatched to the bus, regardless of the outcome,
 * it can be used for resetting services after each message for example
 */
final class EnvelopeDispatchedEvent
{
}
