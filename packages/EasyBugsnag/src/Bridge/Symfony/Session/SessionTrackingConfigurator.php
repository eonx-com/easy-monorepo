<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\Session;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class SessionTrackingConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \Symfony\Contracts\Cache\CacheInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $expiresAfter;

    public function __construct(CacheInterface $cache, ?int $expiresAfter = null, ?int $priority = null)
    {
        $this->cache = $cache;
        $this->expiresAfter = $expiresAfter ?? 3600;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getSessionTracker()
            ->setStorageFunction(function (string $key, $value = null) {
                $callback = function (ItemInterface $item) use ($value) {
                    $item->expiresAfter($this->expiresAfter);

                    return $value;
                };

                // If value not null, we want to expire the item straight away to replace the value
                $beta = $value === null ? 0 : \INF;

                return $this->cache->get($key, $callback, $beta);
            });
    }
}
