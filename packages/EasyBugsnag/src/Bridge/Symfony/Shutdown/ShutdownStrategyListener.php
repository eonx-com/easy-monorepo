<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Shutdown;

use EonX\EasyBugsnag\Shutdown\ShutdownStrategy;

final class ShutdownStrategyListener
{
    /**
     * @var \EonX\EasyBugsnag\Shutdown\ShutdownStrategy
     */
    private $shutdownStrategy;

    public function __construct(ShutdownStrategy $shutdownStrategy)
    {
        $this->shutdownStrategy = $shutdownStrategy;
    }

    public function __invoke(): void
    {
        $this->shutdownStrategy->shutdown();
    }
}
