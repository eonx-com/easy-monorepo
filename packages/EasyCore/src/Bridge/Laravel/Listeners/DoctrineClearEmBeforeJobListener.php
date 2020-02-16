<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Listeners;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Queue\Events\JobProcessing;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class DoctrineClearEmBeforeJobListener
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * DoctrineClearEmBeforeJobListener constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param null|\Psr\Log\LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, ?LoggerInterface $logger = null)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Clear doctrine entity manager before processing job.
     *
     * @param \Illuminate\Queue\Events\JobProcessing $event
     *
     * @return void
     */
    public function handle(JobProcessing $event): void
    {
        $this->logger->info('Clearing em before job', [
            'connection' => $event->connectionName,
            'job_id' => $event->job->getJobId(),
            'job_name' => $event->job->getName()
        ]);

        $this->entityManager->clear();
    }
}
