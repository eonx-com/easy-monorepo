<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Shutdown;

use Bugsnag\Client;
use Bugsnag\Shutdown\ShutdownStrategyInterface;

/**
 * Thanks to https://github.com/bugsnag/bugsnag-symfony for this.
 */
final class ShutdownStrategyListener implements ShutdownStrategyInterface
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    public function __invoke(): void
    {
        if ($this->client === null) {
            return;
        }

        $this->client->flush();
        $this->client->clearBreadcrumbs();
    }

    public function registerShutdownStrategy(Client $client): void
    {
        $this->client = $client;
    }
}
