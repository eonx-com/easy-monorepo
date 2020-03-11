<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Bridge\WithProcessJobLogTrait;
use EonX\EasyAsync\Interfaces\WithProcessJobLogDataInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class ProcessJobLogMiddleware implements MiddlewareInterface
{
    use WithProcessJobLogTrait;

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($this->shouldSkip($envelope)) {
            return $stack->next()->handle($envelope, $stack);
        }

        /** @var \EonX\EasyAsync\Interfaces\WithProcessJobLogDataInterface $message */
        $message = $envelope->getMessage();

        $newEnvelope = $this->processWithJobLog($message, static function () use ($envelope, $stack): Envelope {
            return $stack->next()->handle($envelope, $stack);
        });

        // If exception is thrown during process, trait will return null
        return $newEnvelope ?? $envelope;
    }

    private function shouldSkip(Envelope $envelope): bool
    {
        if ($envelope->last(ConsumedByWorkerStamp::class) === null) {
            return true;
        }

        if ($envelope->getMessage() instanceof WithProcessJobLogDataInterface === false) {
            return true;
        }

        return false;
    }
}
