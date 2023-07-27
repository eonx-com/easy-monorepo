<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Shutdown;

use EonX\EasyBugsnag\Shutdown\ShutdownStrategy;

final class ShutdownStrategyListener
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
