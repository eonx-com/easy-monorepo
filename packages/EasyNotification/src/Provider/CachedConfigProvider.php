<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Provider;

use EonX\EasyNotification\Bundle\Enum\BundleParam;
use EonX\EasyNotification\ValueObject\ConfigInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedConfigProvider implements ConfigProviderInterface
{
    private int $expiresAfter;

    public function __construct(
        private CacheInterface $cache,
        private ConfigProviderInterface $decorated,
        ?int $expiresAfter = null,
    ) {
        $this->expiresAfter = $expiresAfter ?? 3600;
    }

    public function find(string $apiKey, string $providerExternalId): ConfigInterface
    {
        return $this->cache->get(
            BundleParam::ConfigCacheKey->value,
            function (ItemInterface $item) use ($apiKey, $providerExternalId): ConfigInterface {
                $item->expiresAfter($this->expiresAfter);

                return $this->decorated->find($apiKey, $providerExternalId);
            }
        );
    }
}
