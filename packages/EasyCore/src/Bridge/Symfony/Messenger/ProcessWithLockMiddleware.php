<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Messenger;

use EonX\EasyCore\Lock\ProcessWithLockTrait;
use EonX\EasyCore\Lock\WithLockDataInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

/**
 * @deprecated Since 2.4.31. Will be remove in 3.0. Use eonx-com/easy-lock package instead.
 */
final class ProcessWithLockMiddleware implements MiddlewareInterface
{
    use ProcessWithLockTrait;

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        @\trigger_error(\sprintf(
            '%s is deprecated since 2.4.31 and will be removed in 3.0, Use eonx-com/easy-lock package instead.',
            static::class
        ), \E_USER_DEPRECATED);

        if ($this->shouldSkip($envelope)) {
            return $stack->next()
                ->handle($envelope, $stack);
        }

        /** @var \EonX\EasyCore\Lock\WithLockDataInterface $message */
        $message = $envelope->getMessage();

        $newEnvelope = $this->processWithLock($message, static function () use ($envelope, $stack): Envelope {
            return $stack->next()
                ->handle($envelope, $stack);
        });

        return $newEnvelope ?? $envelope;
    }

    private function shouldSkip(Envelope $envelope): bool
    {
        if ($envelope->last(ConsumedByWorkerStamp::class) === null) {
            return true;
        }
        return $envelope->getMessage() instanceof WithLockDataInterface === false;
    }
}
