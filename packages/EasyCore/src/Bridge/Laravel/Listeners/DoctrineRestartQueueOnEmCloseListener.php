<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Listeners;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Support\InteractsWithTime;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class DoctrineRestartQueueOnEmCloseListener
{
    use InteractsWithTime;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * DoctrineRestartQueueOnEmCloseListener constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(EntityManagerInterface $entityManager, Cache $cache, ?LoggerInterface $logger = null)
    {
        $this->entityManager = $entityManager;
        $this->cache = $cache;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Handles JobExceptionOccurred event.
     *
     * @param \Illuminate\Queue\Events\JobExceptionOccurred $event
     */
    public function handle(JobExceptionOccurred $event): void
    {
        if ($this->entityManager->isOpen() === false) {
            $this->cache->forever('illuminate:queue:restart', $this->currentTime());

            $this->logger->info('Restarting queue because em is closed.', [
                'connection' => $event->connectionName,
                'job_id' => $event->job->getJobId(),
                'job_name' => $event->job->getName(),
            ]);
        }
    }
}
