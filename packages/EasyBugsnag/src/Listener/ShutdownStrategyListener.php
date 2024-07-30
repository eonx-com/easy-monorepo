<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Listener;

use EonX\EasyBugsnag\Strategy\ShutdownStrategy;

final readonly class ShutdownStrategyListener
{
    public function __construct(
        private ShutdownStrategy $shutdownStrategy,
    ) {
    }

    public function __invoke(): void
    {
        $this->shutdownStrategy->shutdown();
    }
}
