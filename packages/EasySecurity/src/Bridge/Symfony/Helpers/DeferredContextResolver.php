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

    /**
     * DeferredContextResolver constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string $contextServiceId
     */
    public function __construct(ContainerInterface $container, string $contextServiceId)
    {
        $this->container = $container;
        $this->contextServiceId = $contextServiceId;
    }

    /**
     * Resolve context.
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function resolve(): ContextInterface
    {
        /** @var \EonX\EasySecurity\Interfaces\ContextInterface $context */
        $context = $this->container->get($this->contextServiceId);

        return $context;
    }
}
