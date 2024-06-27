<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Listener;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySchedule\Event\CommandExecutedEvent;

final class CommandExecutedListener
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function __invoke(CommandExecutedEvent $event): void
    {
        foreach ($this->managerRegistry->getManagers() as $entityManager) {
            $entityManager->clear();
        }
    }
}
