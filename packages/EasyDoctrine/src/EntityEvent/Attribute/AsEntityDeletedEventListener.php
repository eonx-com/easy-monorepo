<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Attribute;

use Attribute;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Defines a listener for the "entity deleted" event of a specific entity class.
 *
 * Instead of listening to all EntityDeletedEvent occurrences and filtering by instanceof,
 * this attribute narrows the listener to a single entity type automatically.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class AsEntityDeletedEventListener extends AsEventListener
{
    /**
     * @param class-string $entity The entity class to listen to
     */
    public function __construct(
        string $entity,
        ?string $method = null,
        int $priority = 0,
        ?string $dispatcher = null,
    ) {
        parent::__construct(self::buildEventName($entity), $method, $priority, $dispatcher);
    }

    /**
     * @param class-string $entityClass
     */
    public static function buildEventName(string $entityClass): string
    {
        return 'entity.deleted.' . $entityClass;
    }
}
