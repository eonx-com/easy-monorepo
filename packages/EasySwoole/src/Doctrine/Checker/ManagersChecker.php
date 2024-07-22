<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Checker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySwoole\Common\Checker\AbstractAppStateChecker;

final class ManagersChecker extends AbstractAppStateChecker
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function isApplicationStateCompromised(): bool
    {
        foreach ($this->managerRegistry->getManagers() as $manager) {
            if ($manager instanceof EntityManagerInterface && $manager->isOpen() === false) {
                return true;
            }
        }

        return false;
    }
}
