<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use Psr\Container\ContainerInterface;

interface ApiTokenDecoderFactoryInterface
{
    public function build(string $decoder): ApiTokenDecoderInterface;

    public function setContainer(ContainerInterface $container): void;
}

\class_alias(ApiTokenDecoderFactoryInterface::class, EasyApiTokenDecoderFactoryInterface::class);
