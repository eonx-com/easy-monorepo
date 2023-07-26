<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use EonX\EasyAsync\Doctrine\ManagersSanityChecker;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final class DoctrineManagersSanityCheckMiddleware implements MiddlewareInterface
{
    /**
     * @param null|string[] $managers
     */
    public function __construct(
        private readonly ManagersSanityChecker $managersSanityChecker,
        private readonly ?array $managers = null,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $fromWorker = $envelope->last(ConsumedByWorkerStamp::class);

        if ($fromWorker instanceof ConsumedByWorkerStamp) {
            $this->managersSanityChecker->checkSanity($this->managers);
        }

        return $stack->next()
            ->handle($envelope, $stack);
    }
}
