<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\RequestResolverInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasyUtils\CollectorHelper;

abstract class AbstractSecurityContextFactory implements SecurityContextFactoryInterface
{
    /**
     * @var null|\EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $cached;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \EonX\EasySecurity\Interfaces\RequestResolverInterface
     */
    private $requestResolver;

    /**
     * @param iterable<mixed> $configurators
     */
    public function __construct(iterable $configurators, RequestResolverInterface $requestResolver)
    {
        $this->configurators = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($configurators, SecurityContextConfiguratorInterface::class)
        );

        $this->requestResolver = $requestResolver;
    }

    public function create(): SecurityContextInterface
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $context = $this->doCreate();

        if (\count($this->configurators) > 0) {
            $request = $this->requestResolver->getRequest();

            foreach ($this->configurators as $configurator) {
                $configurator->configure($context, $request);
            }
        }

        return $this->cached = $context;
    }

    public function reset(): void
    {
        $this->cached = null;
    }

    abstract protected function doCreate(): SecurityContextInterface;
}
