<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Listeners;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyCore\Doctrine\DBAL\ConnectionChecker;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\InteractsWithTime;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class DoctrineRestartQueueOnConnectionFailedListener
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

    public function __construct(Cache $cache, EntityManagerInterface $entityManager, ?LoggerInterface $logger = null)
    {
        $this->cache = $cache;
        $this->entityManager = $entityManager;
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(JobProcessing $event): void
    {
        $this->logger->info('Checking doctrine connection before job', [
            'connection' => $event->connectionName,
            'job_id' => $event->job->getJobId(),
            'job_name' => $event->job->getName(),
        ]);

        try {
            ConnectionChecker::checkConnection($this->entityManager->getConnection());
        } catch (\Throwable $throwable) {
            $this->logger->info(\sprintf('Restart worker because connection exception: %s', $throwable->getMessage()));
            $this->cache->forever('illuminate:queue:restart', $this->currentTime());
        }
    }
}
