<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\Factories;

use EonX\EasyPagination\Resolvers\Config\StartSizeConfig;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
final class StartSizeConfigFactory
{
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

    public function __invoke(): StartSizeConfig
    {
        return new StartSizeConfig(
            $this->config['start_attribute'],
            $this->config['start_default'],
            $this->config['size_attribute'],
            $this->config['size_default']
        );
    }
}
