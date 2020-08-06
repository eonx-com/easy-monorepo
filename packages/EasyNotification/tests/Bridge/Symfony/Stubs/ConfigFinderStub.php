<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Bridge\Symfony\Stubs;

use EonX\EasyNotification\Config\Config;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\ConfigInterface;

final class ConfigFinderStub implements ConfigFinderInterface
{
    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @var mixed[] $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function find(): ConfigInterface
    {
        return Config::fromArray($this->config);
    }
}
