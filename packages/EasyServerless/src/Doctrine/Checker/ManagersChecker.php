<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Doctrine\Checker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyServerless\State\Checker\StateCheckerInterface;
use RuntimeException;

final readonly class ManagersChecker implements StateCheckerInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function check(): void
    {
        $managerNames = \array_keys($this->managerRegistry->getManagerNames());

        foreach ($managerNames as $managerName) {
            $manager = $this->managerRegistry->getManager($managerName);

            if ($manager instanceof EntityManagerInterface && $manager->isOpen() === false) {
                throw new RuntimeException(\sprintf(
                    'Entity manager "%s" is closed, application state is compromised.',
                    $managerName
                ));
            }
        }
    }
}
