<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Helpers;

use EonX\EasySecurity\Bridge\Laravel\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use Illuminate\Contracts\Container\Container;

final class DeferredContextResolver implements DeferredContextResolverInterface
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $container;

    /**
     * @var string
     */
    private $contextServiceId;

    public function __construct(Container $container, string $contextServiceId)
    {
        $this->container = $container;
        $this->contextServiceId = $contextServiceId;
    }

    public function resolve(): ContextInterface
    {
        return $this->container->get($this->contextServiceId);
    }
}
