<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\DeferredSecurityContextProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Psr\Container\ContainerInterface;

final class DeferredSecurityContextProvider implements DeferredSecurityContextProviderInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $contextServiceId;

    public function __construct(ContainerInterface $container, string $contextServiceId)
    {
        $this->container = $container;
        $this->contextServiceId = $contextServiceId;
    }

    public function getSecurityContext(): SecurityContextInterface
    {
        return $this->container->get($this->contextServiceId);
    }
}
