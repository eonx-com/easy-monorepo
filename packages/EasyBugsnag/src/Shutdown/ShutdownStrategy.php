<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Shutdown;

use Bugsnag\Client;
use Bugsnag\Shutdown\ShutdownStrategyInterface;

class ShutdownStrategy implements ShutdownStrategyInterface
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    public function shutdown(): void
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

        \register_shutdown_function([$this, 'shutdown']);
    }
}
