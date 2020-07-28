<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface ApiTokenDecoderInterface
{
    public const NAME_BASIC = 'basic';
    public const NAME_CHAIN = 'chain';
    public const NAME_USER_APIKEY = 'user-apikey';
    public const NAME_JWT_HEADER = 'jwt-header';
    public const NAME_JWT_PARAM = 'jwt-param';

    public function getName(): string;

    public function decode(ServerRequestInterface $request): ?ApiTokenInterface;
}

\class_alias(ApiTokenDecoderInterface::class, EasyApiTokenDecoderInterface::class);
