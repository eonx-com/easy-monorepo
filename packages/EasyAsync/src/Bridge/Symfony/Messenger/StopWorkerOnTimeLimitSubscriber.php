<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Exceptions\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Throwable;

final class StopWorkerOnTimeLimitSubscriber implements EventSubscriberInterface
{
    private float $endTime;

    private int $timeLimitInSeconds;

    /**
     * @throws \EonX\EasyAsync\Exceptions\InvalidArgumentException
     */
    public function __construct(
        int $minTimeLimitInSeconds,
        ?int $maxTimeLimitInSeconds = null,
        private LoggerInterface $logger = new NullLogger(),
    ) {
        try {
            $this->timeLimitInSeconds = \random_int(
                $minTimeLimitInSeconds,
                $maxTimeLimitInSeconds ?? $minTimeLimitInSeconds
            );
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerRunningEvent::class => 'onWorkerRunning',
            WorkerStartedEvent::class => 'onWorkerStarted',
        ];
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

    public function onWorkerStarted(): void
    {
        $startTime = \microtime(true);
        $this->endTime = $startTime + $this->timeLimitInSeconds;
    }
}
