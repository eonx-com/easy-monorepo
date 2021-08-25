<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Configurators\DefaultSecurityContextConfigurator;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class SecurityContextResolver implements SecurityContextResolverInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface
     */
    private $authorizationMatrix;

    /**
     * @var null|callable
     */
    private $configurator;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface
     */
    private $factory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var null|\EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(
        AuthorizationMatrixInterface $authorizationMatrix,
        SecurityContextFactoryInterface $factory,
        ?LoggerInterface $logger = null
    ) {
        $this->authorizationMatrix = $authorizationMatrix;
        $this->factory = $factory;
        $this->logger = $logger ?? new NullLogger();
    }

    public function resolveContext(): SecurityContextInterface
    {
        if ($this->securityContext !== null) {
            return $this->securityContext;
        }

        $securityContext = $this->factory->create();
        $securityContext->setAuthorizationMatrix($this->authorizationMatrix);

        $configurator = $this->configurator;
        if ($configurator === null) {
            $configurator = new DefaultSecurityContextConfigurator();

            $this->logger->info(\sprintf(
                'No security context configurator set on %s, make sure to inject security context directly into
                classes that are instantiated only after a configurator is set, otherwise inject %s instead',
                SecurityContextResolverInterface::class,
                SecurityContextResolverInterface::class
            ));
        }

        return $this->securityContext = $configurator($securityContext);
    }

    public function setConfigurator(callable $configurator): SecurityContextResolverInterface
    {
        $this->configurator = $configurator;
        $this->securityContext = null;

        return $this;
    }
}
