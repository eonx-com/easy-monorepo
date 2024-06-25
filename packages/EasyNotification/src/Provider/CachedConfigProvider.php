<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Provider;

use EonX\EasyNotification\ValueObject\ConfigInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        private ConfigProviderInterface $decorated,
        private int $expiresAfter,
        private string $key
    ) {
    }

    public function provide(string $apiKey, string $providerExternalId): ConfigInterface
    {
        return $this->cache->get(
            $this->key,
            function (ItemInterface $item) use ($apiKey, $providerExternalId): ConfigInterface {
                $item->expiresAfter($this->expiresAfter);

                return $this->decorated->provide($apiKey, $providerExternalId);
            }
        );
    }
}
