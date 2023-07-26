<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\Messenger;

use EonX\EasyLock\Interfaces\WithLockDataInterface;
use EonX\EasyLock\ProcessWithLockTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ProcessWithLockMiddleware implements MiddlewareInterface
{
    use ProcessWithLockTrait;

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

        $newEnvelope = $this->processWithLock($withLockData, static fn (): Envelope => $stack->next()
            ->handle($envelope, $stack));

        return $newEnvelope ?? $envelope;
    }

    private function getLockData(Envelope $envelope): ?WithLockDataInterface
    {
        $message = $envelope->getMessage();

        if ($message instanceof WithLockDataInterface) {
            return $message;
        }

        return $envelope->last(WithLockDataStamp::class);
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
