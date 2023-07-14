<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Throwable;

class StopWorkerOnMessagesLimitSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    private int $messagesLimit;

    private int $receivedMessages = 0;

    /**
     * @throws \EonX\EasyAsync\Exceptions\InvalidArgumentException
     */
    public function __construct(int $minMessages, ?int $maxMessages = null, ?LoggerInterface $logger = null)
    {
        try {
            $this->messagesLimit = \random_int($minMessages, $maxMessages ?? $minMessages);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }

        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        // Count only when processing messages
        if ($event->isWorkerIdle()) {
            return;
        }

        if (++$this->receivedMessages >= $this->messagesLimit) {
            $this->receivedMessages = 0;
            $event
                ->getWorker()
                ->stop();

            $this->logger->info('Worker stopped due to maximum count of {count} messages processed', [
                'count' => $this->messagesLimit,
            ]);
        }
    }
}
