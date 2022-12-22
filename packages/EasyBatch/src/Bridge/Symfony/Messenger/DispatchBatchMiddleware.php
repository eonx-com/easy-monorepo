<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Interfaces\BatchInterface;
use EonX\EasyBatch\Interfaces\BatchObjectManagerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final class DispatchBatchMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly BatchObjectManagerInterface $batchObjectManager)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $receivedStamp = $envelope->last(ReceivedStamp::class);
        $message = $envelope->getMessage();

        if ($receivedStamp === null && $message instanceof BatchInterface) {
            $this->batchObjectManager->dispatchBatch($message);

            return $envelope;
        }

        return $stack->next()
            ->handle($envelope, $stack);
    }
}
