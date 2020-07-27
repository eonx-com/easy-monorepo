<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface ApiTokenDecoderInterface
{
    public function decode(ServerRequestInterface $request): ?ApiTokenInterface;
}

\class_alias(ApiTokenDecoderInterface::class, EasyApiTokenDecoderInterface::class);
