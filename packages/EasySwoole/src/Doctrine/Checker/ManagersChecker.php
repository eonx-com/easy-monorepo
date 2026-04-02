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
        return \array_any(
            $this->managerRegistry->getManagers(),
            static fn ($manager): bool => $manager instanceof EntityManagerInterface && $manager->isOpen() === false
        );
    }
}
