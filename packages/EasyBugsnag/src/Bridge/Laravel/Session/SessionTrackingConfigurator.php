<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Laravel\Session;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use Illuminate\Contracts\Cache\Repository;

final class SessionTrackingConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    /**
     * @var int
     */
    private $expiresAfter;

    public function __construct(Repository $cache, int $expiresAfter, ?int $priority = null)
    {
        $this->cache = $cache;
        $this->expiresAfter = $expiresAfter;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getSessionTracker()->setStorageFunction(function (string $key, $value = null) {
            return $value === null
                ? $this->cache->get($key)
                : $this->cache->put($key, $value, $this->expiresAfter);
        });
    }
}
