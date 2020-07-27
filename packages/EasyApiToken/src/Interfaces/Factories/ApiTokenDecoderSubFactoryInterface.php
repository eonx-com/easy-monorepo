<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;

interface ApiTokenDecoderSubFactoryInterface
{
    /**
     * @param null|mixed[] $config
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null): ApiTokenDecoderInterface;
}

\class_alias(ApiTokenDecoderSubFactoryInterface::class, EasyApiTokenDecoderSubFactoryInterface::class);
