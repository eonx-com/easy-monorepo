<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface EasyApiTokenDecoderInterface
{
    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface;
}

\class_alias(
    EasyApiTokenDecoderInterface::class,
    'StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface',
    false
);
