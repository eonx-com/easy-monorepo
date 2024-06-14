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
    private string $message;

    private bool $shouldKillWorker = false;

    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
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
            $this->message = \sprintf('Kill worker because of exception "%s"', $event->getThrowable()::class);
            $this->shouldKillWorker = true;
        }
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if ($this->shouldKillWorker) {
            $this->logger->warning($this->message);

            $event
                ->getWorker()
                ->stop();
        }
    }
}
