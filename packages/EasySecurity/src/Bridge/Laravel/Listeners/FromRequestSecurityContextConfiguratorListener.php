<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Listeners;

use EonX\EasySecurity\Configurators\FromRequestConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Illuminate\Routing\Events\RouteMatched;

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

    public function handle(RouteMatched $event): void
    {
        $this->securityContextResolver->setConfigurator(new FromRequestConfigurator(
            $event->request,
            $this->configurators,
        ));
    }
}
