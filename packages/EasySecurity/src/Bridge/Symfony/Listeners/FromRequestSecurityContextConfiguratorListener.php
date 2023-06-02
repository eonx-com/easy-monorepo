<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Listeners;

use EonX\EasySecurity\Configurators\FromRequestConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class FromRequestSecurityContextConfiguratorListener
{
    /**
     * @var iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface>
     */
    private $configurators;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextResolverInterface
     */
    private $securityContextResolver;

    /**
     * @param iterable<\EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(SecurityContextResolverInterface $securityContextResolver, iterable $configurators)
    {
        $this->securityContextResolver = $securityContextResolver;
        $this->configurators = $configurators;
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $this->securityContextResolver->setConfigurator(new FromRequestConfigurator(
            $event->getRequest(),
            $this->configurators,
        ));
    }
}
