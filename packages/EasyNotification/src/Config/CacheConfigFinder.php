<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Config;

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CacheConfigFinder implements ConfigFinderInterface
{
    /**
     * @var \Symfony\Contracts\Cache\CacheInterface
     */
    private $cache;

    /**
     * @var \EonX\EasyNotification\Interfaces\ConfigFinderInterface
     */
    private $decorated;

    /**
     * @var int
     */
    private $expiresAfter;

    public function __construct(CacheInterface $cache, ConfigFinderInterface $decorated, ?int $expiresAfter = null)
    {
        $this->cache = $cache;
        $this->decorated = $decorated;
        $this->expiresAfter = $expiresAfter ?? BridgeConstantsInterface::CONFIG_CACHE_EXPIRES_AFTER;
    }

    public function find(string $apiKey, string $providerExternalId): ConfigInterface
    {
        $key = \sprintf('%s-%s', BridgeConstantsInterface::CONFIG_CACHE_KEY, $providerExternalId);

        return $this->cache->get(
            $key,
            function (ItemInterface $item) use ($apiKey, $providerExternalId): ConfigInterface {
                $item->expiresAfter($this->expiresAfter);

                return $this->decorated->find($apiKey, $providerExternalId);
            }
        );
    }
}
