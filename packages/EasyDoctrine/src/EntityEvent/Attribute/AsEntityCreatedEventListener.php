<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Attribute;

use Attribute;
use EonX\EasyDoctrine\EntityEvent\Event\EntityCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Defines a listener for the "entity created" event of a specific entity class.
 *
 * Instead of listening to all EntityCreatedEvent occurrences and filtering by instanceof,
 * this attribute narrows the listener to a single entity type automatically.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class AsEntityCreatedEventListener extends AsEventListener
{
    /**
     * @param class-string $entityClass The entity class to listen to
     */
    public function __construct(
        string $entityClass,
        ?string $method = null,
        int $priority = 0,
        ?string $dispatcher = null,
    ) {
        parent::__construct(EntityCreatedEvent::buildEventName($entityClass), $method, $priority, $dispatcher);
    }
}
