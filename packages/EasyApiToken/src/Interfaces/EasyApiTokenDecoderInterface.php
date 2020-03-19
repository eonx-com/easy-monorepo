<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface EasyApiTokenDecoderInterface
{
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface;
}
