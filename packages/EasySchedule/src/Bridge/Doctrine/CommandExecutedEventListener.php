<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySchedule\Events\CommandExecutedEvent;

final readonly class CommandExecutedEventListener
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function __invoke(CommandExecutedEvent $event): void
    {
        foreach ($this->managerRegistry->getManagers() as $entityManager) {
            $entityManager->clear();
        }
    }
}
