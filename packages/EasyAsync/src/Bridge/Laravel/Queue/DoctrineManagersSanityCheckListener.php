<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Queue;

use EonX\EasyAsync\Doctrine\ManagersSanityChecker;
use EonX\EasyAsync\Interfaces\ShouldKillWorkerExceptionInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Events\JobProcessing;
use Psr\Log\LoggerInterface;
use Throwable;

final class DoctrineManagersSanityCheckListener extends AbstractQueueListener
{
    /**
     * @param null|string[] $managers
     */
    public function __construct(
        Cache $cache,
        private ManagersSanityChecker $managersSanityChecker,
        private ?array $managers = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($cache, $logger);
    }

    /**
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineConnectionNotOkException
     * @throws \EonX\EasyAsync\Doctrine\Exceptions\DoctrineManagerClosedException
     * @throws \Throwable
     */
    public function handle(JobProcessing $event): void
    {
        $this->logger->info('Checking doctrine connection before job', [
            'connection' => $event->connectionName,
            'job_id' => $event->job->getJobId(),
            'job_name' => $event->job->getName(),
        ]);

        try {
            $this->managersSanityChecker->checkSanity($this->managers);
        } catch (Throwable $throwable) {
            if ($throwable instanceof ShouldKillWorkerExceptionInterface) {
                $this->killWorker($throwable);
            }

            throw $throwable;
        }
    }
}
