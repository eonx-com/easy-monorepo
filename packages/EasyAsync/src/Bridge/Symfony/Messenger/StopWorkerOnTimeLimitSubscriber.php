<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;

class StopWorkerOnTimeLimitSubscriber implements EventSubscriberInterface
{
    /**
     * @var int
     */
    private $timeLimitInSeconds;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var float
     */
    private $endTime;

    /**
     * @throws \EonX\EasyAsync\Exceptions\InvalidArgumentException
     */
    public function __construct(int $minTimeLimitInSeconds, ?int $maxTimeLimitInSeconds = null, ?LoggerInterface $logger = null)
    {
        try {
            $this->timeLimitInSeconds = \random_int(
                $minTimeLimitInSeconds,
                $maxTimeLimitInSeconds ?? $minTimeLimitInSeconds
            );
        } catch (\Throwable $throwable) {
            throw new InvalidArgumentException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }

        $this->logger = $logger ?? new NullLogger();
    }

    public function onWorkerStarted(): void
    {
        $startTime = \microtime(true);
        $this->endTime = $startTime + $this->timeLimitInSeconds;
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if ($this->endTime < \microtime(true)) {
            $event
                ->getWorker()
                ->stop();

            $this->logger->info('Worker stopped due to time limit of {timeLimit}s exceeded', [
                'timeLimit' => $this->timeLimitInSeconds,
            ]);
        }
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerStartedEvent::class => 'onWorkerStarted',
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }
}
