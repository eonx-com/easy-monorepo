<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Helpers;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class DeferredContextResolver implements DeferredContextResolverInterface
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

    public function resolve(): ContextInterface
    {
        return $this->container->get($this->contextServiceId);
    }
}
