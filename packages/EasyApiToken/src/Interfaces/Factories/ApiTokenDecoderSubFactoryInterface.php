<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;

/**
 * @deprecated since 2.4. Will be removed in 3.0.
 */
interface ApiTokenDecoderSubFactoryInterface
{
    /**
     * @param null|mixed[] $config
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null, ?string $name = null): ApiTokenDecoderInterface;
}

\class_alias(ApiTokenDecoderSubFactoryInterface::class, EasyApiTokenDecoderSubFactoryInterface::class);
