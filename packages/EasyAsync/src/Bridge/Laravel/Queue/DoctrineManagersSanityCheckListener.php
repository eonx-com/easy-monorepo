<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Queue;

use EonX\EasyAsync\Doctrine\ManagersSanityChecker;
use EonX\EasyAsync\Interfaces\ShouldKillWorkerExceptionInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Events\JobProcessing;
use Psr\Log\LoggerInterface;

final class DoctrineManagersSanityCheckListener extends AbstractQueueListener
{
    /**
     * @var null|string[]
     */
    private $managers;

    /**
     * @var \EonX\EasyAsync\Doctrine\ManagersSanityChecker
     */
    private $managersSanityChecker;

    /**
     * @param null|string[] $managers
     */
    public function __construct(
        Cache $cache,
        ManagersSanityChecker $managersSanityChecker,
        ?array $managers = null,
        ?LoggerInterface $logger = null
    ) {
        $this->managersSanityChecker = $managersSanityChecker;
        $this->managers = $managers;

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
        } catch (\Throwable $throwable) {
            if ($throwable instanceof ShouldKillWorkerExceptionInterface) {
                $this->killWorker($throwable);
            }

            throw $throwable;
        }
    }
}
