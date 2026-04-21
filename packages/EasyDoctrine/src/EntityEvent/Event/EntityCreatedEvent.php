<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

/**
 * @template T of object
 *
 * @extends \EonX\EasyDoctrine\EntityEvent\Event\AbstractEntityEvent<T>
 */
final readonly class EntityCreatedEvent extends AbstractEntityEvent
{
}
