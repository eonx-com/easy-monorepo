<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\Listener;

use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class CheckStateOnEnvelopeDispatchedListener
{
    /**
     * @param iterable<\EonX\EasyServerless\State\Checker\StateCheckerInterface> $stateCheckers
     */
    public function __construct(
        private iterable $stateCheckers,
    ) {
    }

    public function __invoke(EnvelopeDispatchedEvent $event): void
    {
        foreach ($this->stateCheckers as $stateChecker) {
            $stateChecker->check();
        }
    }
}
