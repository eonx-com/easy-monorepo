<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use Illuminate\Contracts\Cache\Repository;

final class SessionTrackingConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private Repository $cache,
        private int $expiresAfter,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getSessionTracker()
            ->setStorageFunction(fn (string $key, $value = null) => $value === null
                ? $this->cache->get($key)
                : $this->cache->put($key, $value, $this->expiresAfter));
    }
}
