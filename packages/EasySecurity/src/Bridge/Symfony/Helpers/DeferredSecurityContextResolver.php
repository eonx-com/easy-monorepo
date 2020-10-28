<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Helpers;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredSecurityContextResolverInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Not final on purpose for BC compatibility until 3.0.
 */
class DeferredSecurityContextResolver implements DeferredSecurityContextResolverInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
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

    public function resolve(): SecurityContextInterface
    {
        return $this->container->get($this->contextServiceId);
    }
}
