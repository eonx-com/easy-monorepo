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
     * @var null|string[]
     */
    private $managers;

    /**
     * @var \EonX\EasyAsync\Doctrine\ManagersSanityChecker
     */
    private $managersSanityChecker;

    /**
     * @param null|string[] $managers
     */
    public function __construct(ManagersSanityChecker $managersSanityChecker, ?array $managers = null)
    {
        $this->managersSanityChecker = $managersSanityChecker;
        $this->managers = $managers;
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
