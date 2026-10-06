<?php
declare(strict_types=1);

namespace EonX\EasyLock\Messenger\Middleware;

use EonX\EasyLock\Common\Locker\ProcessWithLockTrait;
use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Common\ValueObject\WithLockDataInterface;
use EonX\EasyLock\Messenger\Stamp\LockNotAcquiredStamp;
use EonX\EasyLock\Messenger\Stamp\WithLockDataStamp;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

final class ProcessWithLockMiddleware implements MiddlewareInterface
{
    use ProcessWithLockTrait;

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly string $lockNotAcquiredLogLevel = LogLevel::WARNING,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($this->shouldSkip($envelope)) {
            return $stack->next()
                ->handle($envelope, $stack);
        }

        $withLockData = $this->getLockData($envelope);

        if ($withLockData === null) {
            return $stack->next()
                ->handle($envelope, $stack);
        }

        $lockData = $withLockData->getLockData();

        $newEnvelope = $this->locker->processWithLock($lockData, static fn (): Envelope => $stack->next()
            ->handle($envelope, $stack));

        // The closure always returns an envelope, so null means the lock was not acquired
        if ($newEnvelope === null) {
            $this->logLockNotAcquired($envelope, $lockData);

            return $envelope->with(new LockNotAcquiredStamp($lockData->getResource(), $lockData->getTtl()));
        }

        return $newEnvelope;
    }

    private function getLockData(Envelope $envelope): ?WithLockDataInterface
    {
        $message = $envelope->getMessage();

        if ($message instanceof WithLockDataInterface) {
            return $message;
        }

        return $envelope->last(WithLockDataStamp::class);
    }

    private function logLockNotAcquired(Envelope $envelope, LockData $lockData): void
    {
        $this->logger->log(
            $this->lockNotAcquiredLogLevel,
            'Message {class} was not handled because the lock "{resource}" is already acquired.'
            . ' The message is removed from the transport.',
            [
                'class' => $envelope->getMessage()::class,
                'message_id' => $envelope->last(TransportMessageIdStamp::class)?->getId(),
                'resource' => $lockData->getResource(),
                'transport' => $envelope->last(ReceivedStamp::class)?->getTransportName(),
                'ttl' => $lockData->getTtl(),
            ]
        );
    }

    private function shouldSkip(Envelope $envelope): bool
    {
        // Skip if not consumed by worker
        if ($envelope->last(ConsumedByWorkerStamp::class) === null) {
            return true;
        }

        // Proceed if message has lock data
        if ($envelope->getMessage() instanceof WithLockDataInterface) {
            return false;
        }

        // Proceed if envelope has stamp with lock data
        if ($envelope->last(WithLockDataStamp::class) !== null) {
            return false;
        }

        // Skip if none of above statements returned
        return true;
    }
}
