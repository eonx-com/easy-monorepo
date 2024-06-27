<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Listener;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySchedule\Event\CommandExecutedEvent;

final readonly class CommandExecutedListener
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
