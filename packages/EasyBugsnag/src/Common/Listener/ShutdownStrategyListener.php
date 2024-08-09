<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Listener;

use EonX\EasyBugsnag\Common\Strategy\ShutdownStrategy;

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
