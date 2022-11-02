<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ManagersCloser
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly ?LoggerInterface $logger = new NullLogger()
    ) {
    }

    /**
     * @param string[]|null $managers
     *
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException
     */
    public function close(?array $managers = null): void
    {
        // If no managers given, default to all
        $managers = $managers ?? \array_keys($this->registry->getManagerNames());

        foreach ($managers as $managerName) {
            $manager = $this->registry->getManager($managerName);
            if ($manager instanceof EntityManagerInterface) {
                $this->registry->getManager($managerName)
                    ->getConnection()
                    ->close();

                continue;
            }

            $this->logger->warning(\sprintf(
                'Type "%s" for manager "%s" not supported by manager closer',
                \get_class($manager),
                $managerName
            ));
        }
    }
}
