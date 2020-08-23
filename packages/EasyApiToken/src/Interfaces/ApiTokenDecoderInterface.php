<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface ApiTokenDecoderInterface
{
    public const NAME_BASIC = 'basic';
    public const NAME_CHAIN = 'chain';
    public const NAME_JWT_HEADER = 'jwt-header';
    public const NAME_JWT_PARAM = 'jwt-param';
    public const NAME_USER_APIKEY = 'user-apikey';

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function decode($request): ?ApiTokenInterface;

    public function getName(): string;
}

\class_alias(ApiTokenDecoderInterface::class, EasyApiTokenDecoderInterface::class);
