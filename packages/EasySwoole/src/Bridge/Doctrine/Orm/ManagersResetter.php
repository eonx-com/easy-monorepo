<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySwoole\AppStateResetters\AbstractAppStateResetter;

final class ManagersResetter extends AbstractAppStateResetter
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

            if ($manager instanceof EntityManagerInterface) {
                $manager->getConnection()
                    ->close();
            }
        }
    }
}
