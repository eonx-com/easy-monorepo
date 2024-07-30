<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Laravel\Configurators;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurator\AbstractClientConfigurator;
use Illuminate\Contracts\Cache\Repository;

final class SessionTrackingClientConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private readonly Repository $cache,
        private readonly int $expiresAfter,
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
