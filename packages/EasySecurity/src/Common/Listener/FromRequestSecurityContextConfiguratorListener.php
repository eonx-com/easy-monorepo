<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Listener;

use EonX\EasySecurity\Common\Configurator\FromRequestConfigurator;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class FromRequestSecurityContextConfiguratorListener
{
    /**
     * @param iterable<\EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(
        private SecurityContextResolverInterface $securityContextResolver,
        private iterable $configurators,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $this->securityContextResolver->setConfigurator(new FromRequestConfigurator(
            $event->getRequest(),
            $this->configurators
        ));
    }
}
