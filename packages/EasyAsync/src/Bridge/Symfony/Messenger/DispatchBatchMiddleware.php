<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Interfaces\Batch\BatchDispatcherInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
final class DispatchBatchMiddleware implements MiddlewareInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchDispatcherInterface
     */
    private $dispatcher;

    public function __construct(BatchDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        // Act only when not consume from worker
        if ($envelope->last(ConsumedByWorkerStamp::class) === null) {
            $message = $envelope->getMessage();

            if ($message instanceof BatchInterface) {
                $this->dispatcher->dispatch($message);

                // Do not proceed with normal flow, handled by the batch dispatcher
                return $envelope;
            }
        }

        return $stack
            ->next()
            ->handle($envelope, $stack);
    }
}
