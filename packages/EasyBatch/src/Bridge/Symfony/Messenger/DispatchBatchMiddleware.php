<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Messenger;

use EonX\EasyBatch\Interfaces\BatchDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class DispatchBatchMiddleware implements MiddlewareInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchDispatcherInterface
     */
    private $batchDispatcher;

    public function __construct(BatchDispatcherInterface $batchDispatcher)
    {
        $this->batchDispatcher = $batchDispatcher;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // Act only when not consume from worker
        if ($envelope->last(ConsumedByWorkerStamp::class) === null) {
            $message = $envelope->getMessage();

            if ($message instanceof BatchInterface) {
                $this->batchDispatcher->dispatch($message);

                // Do not proceed with normal flow, handled by the batch dispatcher
                return $envelope;
            }
        }

        return $stack
            ->next()
            ->handle($envelope, $stack);
    }
}
