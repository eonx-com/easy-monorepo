<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Orm;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySwoole\AbstractApplicationStateResetter;

final class ManagersResetter extends AbstractApplicationStateResetter
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        ?int $priority = null
    ) {
        parent::__construct($priority);
    }

    public function resetState(): void
    {
        foreach ($this->managerRegistry->getManagers() as $manager) {
            $manager->clear();
        }
    }
}
