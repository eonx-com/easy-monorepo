<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException;
use EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ManagersSanityChecker
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry, ?LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param null|string[] $managers
     *
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException
     */
    public function checkSanity(?array $managers = null): void
    {
        // If no managers given, default to all
        $managers = $managers ?? \array_keys($this->registry->getManagerNames());

        foreach ($managers as $managerName) {
            $manager = $this->registry->getManager($managerName);

            if ($manager instanceof EntityManagerInterface) {
                $this->checkEntityManager($manager, $managerName);

                continue;
            }

            $this->logger->warning(\sprintf(
                'Type "%s" for manager "%s" not supported by sanity checker',
                \get_class($manager),
                $managerName,
            ));
        }
    }

    /**
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException
     */
    private function checkEntityManager(EntityManagerInterface $entityManager, string $name): void
    {
        // Check if closed
        if ($entityManager->isOpen() === false) {
            throw new DoctrineManagerClosedException(\sprintf('Manager "%s" closed', $name));
        }

        // Check connection ok
        try {
            $conn = $entityManager->getConnection();
            $conn->fetchAllAssociative($conn->getDatabasePlatform()->getDummySelectSQL());
        } catch (\Throwable $throwable) {
            throw new DoctrineConnectionNotOkException(
                \sprintf('Connection for manager "%s" not ok: %s', $name, $throwable->getMessage()),
                \is_string($throwable->getCode()) ? 0 : $throwable->getCode(),
                $throwable,
            );
        }
    }
}
