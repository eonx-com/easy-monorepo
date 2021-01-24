<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Messenger;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

final class StopWorkerOnEmClosedEventListener
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $managers;

    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    private $registry;

    /**
     * @param null|string[] $managers
     */
    public function __construct(ManagerRegistry $registry, LoggerInterface $logger, ?array $managers = null)
    {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->managers = $managers ?? ['default'];
    }

    public function __invoke(WorkerRunningEvent $event): void
    {
        foreach ($this->managers as $name) {
            $manager = $this->getEntityManager($name);

            // If manager found and is closed, stop worker
            if ($manager !== null && $manager->isOpen() === false) {
                $event->getWorker()
                    ->stop();

                return;
            }
        }
    }

    private function getEntityManager(string $name): ?EntityManagerInterface
    {
        $entityManager = null;

        try {
            $entityManager = $this->registry->getManager($name);
        } catch (\InvalidArgumentException $exception) {
            $this->logger->info($exception->getMessage());
        }

        return $entityManager instanceof EntityManagerInterface ? $entityManager : null;
    }
}
