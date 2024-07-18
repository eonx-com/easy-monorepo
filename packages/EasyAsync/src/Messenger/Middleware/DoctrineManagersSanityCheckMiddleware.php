<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Middleware;

use EonX\EasyAsync\Doctrine\Checker\ManagersSanityChecker;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;

final readonly class DoctrineManagersSanityCheckMiddleware implements MiddlewareInterface
{
    /**
     * @param string[]|null $managers
     */
    public function __construct(
        private ManagersSanityChecker $managersSanityChecker,
        private ?array $managers = null,
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
