<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stub\Provider;

use EonX\EasyNotification\Provider\ConfigProviderInterface;
use EonX\EasyNotification\ValueObject\Config;
use EonX\EasyNotification\ValueObject\ConfigInterface;

final class ConfigProviderStub implements ConfigProviderInterface
{
    private int $called = 0;

    public function __construct(
        private readonly array $config,
    ) {
    }

    public function getCalled(): int
    {
        return $this->called;
    }

    public function provide(string $apiKey, string $providerExternalId): ConfigInterface
    {
        $this->called++;

        return Config::fromArray($this->config);
    }
}