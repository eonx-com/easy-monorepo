<?php
declare(strict_types=1);

namespace EonX\EasyServerless\State\Listener;

use Symfony\Component\HttpKernel\Event\TerminateEvent;

final readonly class CheckStateListener
{
    /**
     * @param iterable<\EonX\EasyServerless\State\Checker\StateCheckerInterface> $stateCheckers
     */
    public function __construct(
        private iterable $stateCheckers,
    ) {
    }

    public function __invoke(TerminateEvent $event): void
    {
        foreach ($this->stateCheckers as $stateChecker) {
            $stateChecker->check();
        }
    }
}
