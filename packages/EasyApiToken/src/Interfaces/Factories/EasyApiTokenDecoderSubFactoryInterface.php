<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;

interface EasyApiTokenDecoderSubFactoryInterface
{
    /**
     * @param null|mixed[] $config
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface;
}
