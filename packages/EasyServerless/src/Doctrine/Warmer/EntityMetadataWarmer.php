<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Doctrine\Warmer;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;

/**
 * Loads the metadata of all the entities of each manager.
 */
final readonly class EntityMetadataWarmer implements SnapStartWarmerInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function warm(): void
    {
        foreach ($this->managerRegistry->getManagers() as $manager) {
            $manager->getMetadataFactory()
                ->getAllMetadata();
        }
    }
}
