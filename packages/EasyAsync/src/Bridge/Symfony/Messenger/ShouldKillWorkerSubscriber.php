<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Interfaces\ShouldKillWorkerExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

final class ShouldKillWorkerSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $shouldKillWorker = false;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onWorkerMessageFailed',
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }

    public function onWorkerMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if ($event->getThrowable() instanceof ShouldKillWorkerExceptionInterface) {
            $this->logger->info(\sprintf(
                'Kill worker because of exception "%s"',
                \get_class($event->getThrowable())
            ));

            $this->shouldKillWorker = true;
        }
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if ($this->shouldKillWorker) {
            $event->getWorker()
                ->stop();
        }
    }
}
