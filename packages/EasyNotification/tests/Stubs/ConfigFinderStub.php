<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Stubs;

use EonX\EasyNotification\Config\Config;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\ConfigInterface;

final class ConfigFinderStub implements ConfigFinderInterface
{
    /**
     * @var int
     */
    private $called = 0;

    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function find(string $apiKey, string $providerExternalId): ConfigInterface
    {
        $this->called++;

        return Config::fromArray($this->config);
    }

    public function getCalled(): int
    {
        return $this->called;
    }
}
