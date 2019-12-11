<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface EasyApiTokenDecoderInterface
{
    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?EasyApiTokenInterface;
}
