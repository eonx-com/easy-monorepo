<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Configurators\DefaultSecurityContextConfigurator;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class SecurityContextResolver implements SecurityContextResolverInterface
{
    /**
     * @var null|callable
     */
    private $configurator;

    private ?SecurityContextInterface $securityContext = null;

    public function __construct(
        private readonly AuthorizationMatrixFactoryInterface $authorizationMatrixFactory,
        private readonly SecurityContextFactoryInterface $factory,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function resolveContext(): SecurityContextInterface
    {
        if ($this->securityContext !== null) {
            return $this->securityContext;
        }

        $securityContext = $this->factory->create();
        $securityContext->setAuthorizationMatrix($this->authorizationMatrixFactory->create());

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
