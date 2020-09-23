<?php

declare(strict_types=1);

namespace EonX\EasyRequestId;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Psr\Container\ContainerInterface;

final class DeferredRequestIdServiceProvider
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getRequestIdService(): RequestIdServiceInterface
    {
        return $this->container->get(RequestIdServiceInterface::class);
    }
}
