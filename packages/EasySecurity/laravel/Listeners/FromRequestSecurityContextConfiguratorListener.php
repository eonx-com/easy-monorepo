<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Laravel\Listeners;

use EonX\EasySecurity\Common\Configurator\FromRequestConfigurator;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use Illuminate\Routing\Events\RouteMatched;

final readonly class FromRequestSecurityContextConfiguratorListener
{
    /**
     * @param iterable<\EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface> $configurators
     */
    public function __construct(
        private SecurityContextResolverInterface $securityContextResolver,
        private iterable $configurators,
    ) {
    }

    public function handle(RouteMatched $event): void
    {
        $this->securityContextResolver->setConfigurator(new FromRequestConfigurator(
            $event->request,
            $this->configurators
        ));
    }
}
