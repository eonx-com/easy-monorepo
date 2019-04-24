<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface EasyApiTokenDecoderInterface
{
    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface;
}

\class_alias(
    EasyApiTokenDecoderInterface::class,
    'LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface',
    false
);
