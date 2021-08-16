<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Exceptions\NoConfiguratorException;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;

final class SecurityContextResolver implements SecurityContextResolverInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface
     */
    private $authorizationMatrix;

    /**
     * @var callable
     */
    private $configurator;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface
     */
    private $factory;

    /**
     * @var null|\EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(
        AuthorizationMatrixInterface $authorizationMatrix,
        SecurityContextFactoryInterface $factory
    ) {
        $this->authorizationMatrix = $authorizationMatrix;
        $this->factory = $factory;
    }

    public function resolveContext(): SecurityContextInterface
    {
        if ($this->securityContext !== null) {
            return $this->securityContext;
        }

        if ($this->configurator === null) {
            throw new NoConfiguratorException(\sprintf(
                'No security context configurator set on %s, make sure to inject security context directly into
                classes that are instantiated only after a configurator is set, otherwise inject %s instead',
                SecurityContextResolverInterface::class,
                SecurityContextResolverInterface::class
            ));
        }

        $securityContext = $this->factory->create();
        $securityContext->setAuthorizationMatrix($this->authorizationMatrix);

        return $this->securityContext = \call_user_func($this->configurator, $securityContext);
    }

    public function setConfigurator(callable $configurator): SecurityContextResolverInterface
    {
        $this->configurator = $configurator;
        $this->securityContext = null;

        return $this;
    }
}
