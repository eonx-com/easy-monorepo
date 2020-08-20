<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;

interface ApiTokenDecoderFactoryInterface
{
    public function build(string $decoder): ApiTokenDecoderInterface;

    public function buildDefault(): ApiTokenDecoderInterface;
}

\class_alias(ApiTokenDecoderFactoryInterface::class, EasyApiTokenDecoderFactoryInterface::class);
